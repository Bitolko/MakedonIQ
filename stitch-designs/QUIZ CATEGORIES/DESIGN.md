---
name: MakedonIQ
colors:
  surface: '#faf9f4'
  surface-dim: '#dbdad5'
  surface-bright: '#faf9f4'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f5f4ef'
  surface-container: '#efeee9'
  surface-container-high: '#e9e8e3'
  surface-container-highest: '#e3e3de'
  on-surface: '#1b1c19'
  on-surface-variant: '#5e3f3a'
  inverse-surface: '#30312e'
  inverse-on-surface: '#f2f1ec'
  outline: '#936e69'
  outline-variant: '#e8bcb5'
  surface-tint: '#c00000'
  primary: '#a40000'
  on-primary: '#ffffff'
  primary-container: '#d20000'
  on-primary-container: '#ffe1dc'
  inverse-primary: '#ffb4a8'
  secondary: '#705d00'
  on-secondary: '#ffffff'
  secondary-container: '#fcd400'
  on-secondary-container: '#6e5c00'
  tertiary: '#4e4d64'
  on-tertiary: '#ffffff'
  tertiary-container: '#66657d'
  on-tertiary-container: '#e7e4ff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdad4'
  primary-fixed-dim: '#ffb4a8'
  on-primary-fixed: '#410000'
  on-primary-fixed-variant: '#930000'
  secondary-fixed: '#ffe16d'
  secondary-fixed-dim: '#e9c400'
  on-secondary-fixed: '#221b00'
  on-secondary-fixed-variant: '#544600'
  tertiary-fixed: '#e2e0fc'
  tertiary-fixed-dim: '#c6c4df'
  on-tertiary-fixed: '#1a1a2e'
  on-tertiary-fixed-variant: '#45455b'
  background: '#faf9f4'
  on-background: '#1b1c19'
  surface-variant: '#e3e3de'
typography:
  display-lg:
    fontFamily: Montserrat
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: Montserrat
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
  headline-md:
    fontFamily: Montserrat
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Quicksand
    fontSize: 18px
    fontWeight: '500'
    lineHeight: 28px
  body-md:
    fontFamily: Quicksand
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.05em
rounded:
  sm: 0.5rem
  DEFAULT: 1rem
  md: 1.5rem
  lg: 2rem
  xl: 3rem
  full: 9999px
spacing:
  unit: 8px
  container-max: 1200px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 48px
---

## Brand & Style

The design system is centered on the concept of "Heritage through Play." It bridges the gap between traditional Macedonian culture and the modern Australian educational landscape. The visual style is **Corporate Modern with a Gamified Twist**, taking cues from high-end educational platforms like Duolingo but maintaining a premium, structured feel suitable for all ages—from children to grandparents.

The UI should evoke feelings of warmth, pride, and intellectual curiosity. It avoids the heavy, ornate aesthetics often associated with historical archives in favor of a clean, high-performance interface that feels "tech-forward." Use generous white space to allow the vibrant primary colors to act as focal points for gamification elements.

## Colors

The palette is derived from the Macedonian flag but refined for digital screens.
- **Primary (Heritage Red):** Used for critical actions, progress indicators, and branding moments.
- **Secondary (Vibrant Gold):** Used for rewards, achievements, and "Level Up" states. It should be used as an accent to avoid visual fatigue.
- **Tertiary (Deep Navy):** Provides high-contrast legibility for body text and navigation headers. It replaces pure black to add a "premium" depth.
- **Backgrounds:** Use a "Cream-Grey" (#F9F8F3) for the main application canvas to reduce eye strain, while using pure white for elevated card components.
- **Accents:** Success states use a soft, organic green; informational prompts use a clear, educational blue.

## Typography

This design system uses a tri-font strategy to balance character and utility. 
- **Montserrat** is the voice of the brand, used for headings and titles to provide a bold, confident foundation.
- **Quicksand** is used for body text and quiz questions; its rounded terminals maintain a friendly, approachable tone that makes learning feel less intimidating.
- **Inter** is utilized for functional UI elements like labels, tooltips, and data points, ensuring maximum legibility at small sizes.

For the Macedonian Cyrillic script, ensure the fonts support the specific glyphs (e.g., ѓ, ќ, ѕ) with consistent weights to their Latin counterparts.

## Layout & Spacing

The design system follows a **Fixed Grid** philosophy for desktop to maintain a "card-centric" app feel, while transitioning to a fluid model for mobile devices. 
- **The Rhythm:** Use an 8px base unit. All padding and margins should be multiples of 8 (e.g., 16, 24, 32, 64).
- **Desktop:** 12-column grid with a 1200px max-width. Content is centered.
- **Mobile:** 4-column grid. Margins are set at 16px to maximize real estate for quiz interaction buttons.
- **Reflow:** Cards that appear in a 3-column row on desktop should stack vertically on mobile. Use "Large" spacing (32px+) between distinct sections to emphasize the "clean and airy" premium feel.

## Elevation & Depth

To achieve the "Soft Card" aesthetic, this design system avoids harsh shadows. Instead, it uses **Tonal Layering** combined with **Ambient Shadows**.
- **Level 0 (Canvas):** The base background (#F9F8F3).
- **Level 1 (Cards/Surface):** Pure White (#FFFFFF) with a very soft, diffused shadow (0px 4px 20px, 4% opacity Tertiary Color).
- **Level 2 (Active/Hover):** Cards lift slightly with a more defined shadow (0px 8px 30px, 8% opacity Tertiary Color).
- **Interactive Depth:** Buttons should use a 2px bottom-border (solid) in a slightly darker shade of the button color to create a "tactile" pressable feel, similar to modern gaming UIs.

## Shapes

The shape language is defined by **Pill-shaped** and **Extra-Rounded** corners to reinforce the friendly and safe nature of the platform.
- **Primary Buttons:** Fully rounded (pill) to encourage clicking.
- **Quiz Cards:** Use `rounded-2xl` (1.5rem) or `rounded-3xl` (2rem) to create a soft, inviting container for content.
- **Input Fields:** Standardized at `rounded-xl` (1rem) to balance the softness with professional structure.
- **Selection States:** When a card is selected (e.g., a quiz answer), the border-width should increase to 3px using the Primary Red or Success Green, rather than changing the shape.

## Components

- **Buttons:** Primary buttons are Heritage Red with White text. Secondary buttons use a Gold background with Tertiary Navy text. Use a "thick" 4px bottom shadow of a darker shade to give a 3D effect.
- **Quiz Cards:** Large, white surfaces with center-aligned typography. Include a small icon or illustration placeholder.
- **Progress Bar:** A rounded track (Tertiary color at 10% opacity) with a Primary Red fill. Use a Secondary Gold "star" or "sparkle" icon at the end of the progress line.
- **Chips/Badges:** Used for difficulty levels (Easy, Medium, Hard). These should be pill-shaped with low-opacity background fills of the accent colors.
- **Input Fields:** Minimalist with a focus on the active state. When focused, the border should transition to Primary Red with a soft glow.
- **Navigation:** A clean top-bar for desktop and a bottom-tab bar for mobile. Icons should be "Duotone" style, using Tertiary Navy for the main body and Primary Red for accent details.
- **Feedback Overlays:** Success (Correct) and Error (Incorrect) states should take over the bottom 20% of the screen with a slide-up animation, providing immediate, high-contrast feedback.