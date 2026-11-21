"""Typesetting helpers for the logo: harfbuzz shaping + fontTools outlines -> SVG paths. Used by build.py."""
import sys, os
import uharfbuzz as hb
from fontTools.ttLib import TTFont
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.recordingPen import RecordingPen
from fontTools.pens.transformPen import TransformPen
from fontTools.pens.boundsPen import BoundsPen

FD = sys.argv[1]; OUT = sys.argv[2]
NAVY, TEAL = "#0B2F4F", "#0E9AA7"
_cache = {}
def font(name):
    if name not in _cache:
        path = os.path.join(FD, name + ".ttf")
        blob = hb.Blob.from_file_path(path)
        _cache[name] = (TTFont(path), hb.Font(hb.Face(blob)))
    return _cache[name]

def run(name, text, wght, size, x, y, tracking=0.0):
    """Return list of (contours, glyph_name, x0) items and total advance. tracking in em."""
    tt, hf = font(name)
    hf.set_variations({"wght": wght})
    buf = hb.Buffer(); buf.add_str(text); buf.guess_segment_properties()
    hb.shape(hf, buf, {"kern": True, "liga": False})
    upm = tt["head"].unitsPerEm; sc = size / upm
    gs = tt.getGlyphSet(location={"wght": wght}); order = tt.getGlyphOrder()
    pos = 0; glyphs = []
    for info, p in zip(buf.glyph_infos, buf.glyph_positions):
        g = order[info.codepoint]
        rec = RecordingPen(); gs[g].draw(rec)
        # split recording into contours
        contours, cur = [], []
        for op in rec.value:
            cur.append(op)
            if op[0] in ("closePath", "endPath"): contours.append(cur); cur = []
        gx = x + (pos + p.x_offset) * sc
        glyphs.append(dict(name=g, contours=contours, tf=(sc, 0, 0, -sc, gx, y), x=gx, w=p.x_advance * sc))
        pos += p.x_advance + tracking * upm
    return glyphs, (pos - tracking * upm) * sc

def contour_d(contour, tf):
    pen = SVGPathPen(None); tp = TransformPen(pen, tf)
    for op, args in contour: getattr(tp, op)(*args)
    return pen.getCommands()

def contour_top(contour, tf):
    bp = BoundsPen(None); tp = TransformPen(bp, tf)
    for op, args in contour: getattr(tp, op)(*args)
    return bp.bounds

def counter_centre(g, y, b):
    """Centre of the u's counter: midpoint of the two inner stem edges on the scanline y."""
    from fontTools.pens.pointInsidePen import PointInsidePen
    pen = PointInsidePen(None, (0, 0))
    x0 = min(v[0] for v in b); x1 = max(v[2] for v in b)
    sc, _, _, nsc, gx, gy = g["tf"]
    inside = []
    steps = 400
    for i in range(steps + 1):
        x = x0 + (x1 - x0) * i / steps
        # map back to glyph units
        ux, uy = (x - gx) / sc, (y - gy) / nsc
        p = PointInsidePen(None, (ux, uy))
        for c in g["contours"]:
            for op, args in c: getattr(p, op)(*args)
        inside.append(p.getResult())
    xs = [x0 + (x1 - x0) * i / steps for i in range(steps + 1)]
    edges = [xs[i] for i in range(1, len(inside)) if inside[i] != inside[i - 1]]
    # edges: [L outer, L inner, R inner, R outer]
    return (edges[1] + edges[2]) / 2 if len(edges) >= 4 else (x0 + x1) / 2

def paint(glyphs, fill, idot=None, bubble_first_u=None):
    """Emit paths; optionally recolour i/j dots and add a bubble over the first u."""
    out = []; done_u = False
    for g in glyphs:
        cs = g["contours"]
        if idot and g["name"] in ("i", "j") and len(cs) >= 2:
            # the dot is the contour whose top is highest (smallest y in SVG space)
            tops = [contour_top(c, g["tf"])[1] for c in cs]
            k = tops.index(min(tops))
            out.append(f'<path fill="{idot}" d="{contour_d(cs[k], g["tf"])}"/>')
            cs = [c for i, c in enumerate(cs) if i != k]
        out.append(f'<path fill="{fill}" d="{"".join(contour_d(c, g["tf"]) for c in cs)}"/>')
        if bubble_first_u and not done_u and g["name"] == "u":
            b = [contour_top(c, g["tf"]) for c in g["contours"]]
            top = min(v[1] for v in b); bot = max(v[3] for v in b)
            cx = counter_centre(g, top + (bot - top) * 0.35, b)
            r, gap, col = bubble_first_u
            out.append(f'<circle fill="{col}" cx="{cx:.2f}" cy="{top-gap-r:.2f}" r="{r:.2f}"/>')
            done_u = True
    return "".join(out)

def svg(w, h, body, title="Underwater Federation CRM"):
    return f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {w:.0f} {h:.0f}" role="img"><title>{title}</title>{body}</svg>\n'

def save(name, w, h, body):
    open(os.path.join(OUT, name), "w").write(svg(w, h, body))
