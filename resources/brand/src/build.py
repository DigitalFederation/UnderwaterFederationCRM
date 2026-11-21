"""Build the Underwater Federation CRM logo masters (Nunito wordmark + u-bubble symbol)."""
import sys, os
sys.argv = [sys.argv[0], sys.argv[1], sys.argv[2]]
HERE = os.path.dirname(os.path.abspath(__file__))
exec(open(os.path.join(HERE, "typeset.py")).read())

NAVY, TEAL, TEAL_LIGHT, WHITE, BLACK = "#0B2F4F", "#0E9AA7", "#22C3D6", "#FFFFFF", "#000000"
W1 = 850  # "underwater" weight
def wordmark(navy, teal, pad=20):
    g1, w1 = run("Nunito", "underwater", W1, 120, pad, 150, -0.015)
    g2, w2 = run("Nunito", "federation crm", 700, 52, pad + 3, 222, 0.01)
    body = paint(g1, navy, bubble_first_u=(16, 10, teal)) + paint(g2, navy, idot=teal)
    return body, max(w1, w2) + 2 * pad, 245

for tag, (n, t) in {"": (NAVY, TEAL), "-reversed": (WHITE, TEAL_LIGHT), "-black": (BLACK, BLACK), "-white": (WHITE, WHITE)}.items():
    body, w, h = wordmark(n, t)
    save(f"wordmark{tag}.svg", w, h, body)

def symbol_body(navy, teal, weight=W1, r_ratio=16/120, gap_ratio=10/120, box=256, fill_h=0.80):
    """The wordmark's u + bubble, centred in a square."""
    size = 120.0
    g, _ = run("Nunito", "u", weight, size, 0, 0)
    u = g[0]
    b = [contour_top(c, u["tf"]) for c in u["contours"]]
    x0 = min(v[0] for v in b); x1 = max(v[2] for v in b); top = min(v[1] for v in b); bot = max(v[3] for v in b)
    r, gap = r_ratio * size, gap_ratio * size
    cx = counter_centre(u, top + (bot - top) * 0.35, b)
    btop = top - gap - 2 * r
    H = bot - btop; Wd = x1 - x0
    s = box * fill_h / max(H, Wd)
    # centre: horizontally on the u's counter axis keeps the bubble on the canvas axis
    tx = box / 2 - cx * s; ty = box / 2 - (btop + H / 2) * s
    d = "".join(contour_d(c, u["tf"]) for c in u["contours"])
    return (f'<g transform="translate({tx:.2f} {ty:.2f}) scale({s:.4f})">'
            f'<path fill="{navy}" d="{d}"/><circle fill="{teal}" cx="{cx:.2f}" cy="{top-gap-r:.2f}" r="{r:.2f}"/></g>')

for tag, (n, t) in {"": (NAVY, TEAL), "-reversed": (WHITE, TEAL_LIGHT), "-black": (BLACK, BLACK)}.items():
    save(f"symbol{tag}.svg", 256, 256, symbol_body(n, t))
    # small-size cut: heavier u, bigger bubble and gap
    save(f"symbol-small{tag}.svg", 256, 256, symbol_body(n, t, weight=1000, r_ratio=21/120, gap_ratio=15/120, fill_h=0.86))

tile_inner = symbol_body(WHITE, TEAL_LIGHT, weight=1000, r_ratio=21/120, gap_ratio=15/120, fill_h=0.86)
save("app-icon.svg", 256, 256, f'<rect width="256" height="256" rx="56" fill="{NAVY}"/><g transform="translate(128 128) scale(.68) translate(-128 -128)">{tile_inner}</g>')
save("app-icon-maskable.svg", 256, 256, f'<rect width="256" height="256" fill="{NAVY}"/><g transform="translate(128 128) scale(.56) translate(-128 -128)">{tile_inner}</g>')

print("ok")
