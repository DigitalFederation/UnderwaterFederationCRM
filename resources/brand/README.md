# Underwater Federation CRM — logo usage

## The logo
The name is the logo. A bubble rises from the first **u** of "underwater" and returns as the dot on the **i** of
"federation". The icon is that same u and bubble, cut from the wordmark, so the two always match.

## Files
| Use | File |
|---|---|
| Wordmark (primary) | `masters/wordmark.svg` (+ `-reversed`, `-black`, `-white`) |
| Icon | `masters/symbol.svg` (+ `-reversed`, `-black`) |
| Small-size icon (24 px and below) | `masters/symbol-small.svg` (+ `-reversed`, `-black`) |
| App icon | `masters/app-icon.svg`, `masters/app-icon-maskable.svg` |
| Favicons and app icons in use | `public/favicon.ico`, `public/favicon-*.png`, `public/apple-touch-icon.png`, `public/android-chrome-*.png`, `public/images/icons/` |
| Copies served by the docs site | `docs/public/brand/` |

## Colour
| Name | HEX | RGB | Use |
|---|---|---|---|
| Deep Sea | `#0B2F4F` | 11, 47, 79 | The u, the wordmark, dark backgrounds |
| Lagoon | `#0E9AA7` | 14, 154, 167 | The bubble on light backgrounds |
| Lagoon Light | `#22C3D6` | 34, 195, 214 | The bubble on Deep Sea backgrounds |
| White | `#FFFFFF` | 255, 255, 255 | The u and wordmark on dark backgrounds |

Contrast against white: Deep Sea 13.7:1, Lagoon 3.4:1 (meets the 3:1 for graphics). Lagoon Light on Deep Sea: 6.4:1.
Print values (CMYK/Pantone) have not been specified; match from the HEX values with your printer.

## Typography
Set in **Nunito** (SIL Open Font License 1.1, see `NUNITO-OFL.txt`), converted to outlines:
"underwater" at weight 850, "federation crm" at weight 700. Do not retype it; use the files.
For UI text around the logo, Nunito or the app's own font both work.

## Clear space and minimum size
- Clear space on every side: at least the height of the "u" (x-height of "underwater").
- Symbol: minimum 16 px. Use the small-size cut at 24 px and below.
- Wordmark: minimum 120 px wide on screen, 30 mm in print.

## Don't
- Recolour the u and bubble outside the palette, or swap their colours.
- Move the bubble off the u's centre line or onto an arm; that resembles TUI's smile mark.
- Use the icon alone where there is room for the wordmark: the dot-over-u on its own is a common shape.
- Stretch, rotate, outline, add shadows or gradients.
- Put the colour version on busy photos or mid-tone backgrounds; use the reversed or one-colour version.

## Notes
- Rebuild the masters with `python3 src/build.py <fonts-dir> masters`, where `<fonts-dir>` holds `Nunito.ttf`
  (the variable font from [google/fonts](https://github.com/google/fonts/tree/main/ofl/nunito)). It needs the
  `fonttools` and `uharfbuzz` Python packages. Render PNGs from the masters with `rsvg-convert`.
- This is the product's own logo. A deployment's federation logo is set separately with `FEDERATION_LOGO_PATH`.
- No formal trademark search has been done. A web check found no close match.
