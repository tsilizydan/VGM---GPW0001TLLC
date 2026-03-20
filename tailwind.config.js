/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './app/**/*.php',
    './resources/**/*.{css,js}',
    './public/**/*.php',
  ],

  theme: {
    extend: {

      // ── Color Palette ─────────────────────────────────────
      colors: {
        vanilla: {
          50:  '#FDF9F7',
          100: '#F8F1EC',
          200: '#EFD9CD',
          300: '#DEB99E',
          400: '#C48B68',
          500: '#A06642',
          600: '#7A4E33',
          700: '#4E342E', // PRIMARY
          800: '#3B2420',
          900: '#271814',
        },
        cream: {
          50:  '#FEFEFE',
          100: '#F8F5F0', // SECONDARY
          200: '#EDE7DC',
          300: '#DDD3C5',
          400: '#C9BAA7',
          500: '#B09B87',
        },
        forest: {
          50:  '#F2F7EE',
          100: '#E0EDD7',
          200: '#BDDA9E',
          300: '#94BF68',
          400: '#6A8F4E', // ACCENT
          500: '#4F6E39',
          600: '#395029',
          700: '#28391C',
          800: '#1B2612',
          900: '#0F1609',
        },
        gold: {
          50:  '#FDF9EC',
          100: '#F9EFCC',
          200: '#EEDB8E',
          300: '#DFC154',
          400: '#C8A96A', // LUXURY ACCENT
          500: '#A8893C',
          600: '#7D6428',
          700: '#57431A',
          800: '#352A10',
          900: '#1A1408',
        },
        glass: {
          white:  'rgba(255,255,255,0.12)',
          dark:   'rgba(30,20,15,0.15)',
          border: 'rgba(255,255,255,0.20)',
        },
      },

      // ── Typography ────────────────────────────────────────
      fontFamily: {
        serif: ['"Playfair Display"', 'Georgia', 'serif'],
        sans:  ['"Inter"', 'system-ui', 'sans-serif'],
        mono:  ['"JetBrains Mono"', 'monospace'],
      },

      fontSize: {
        'display': ['4.5rem',  { lineHeight: '1.05', letterSpacing: '-0.03em', fontWeight: '800' }],
        'h1':      ['3.25rem', { lineHeight: '1.1',  letterSpacing: '-0.025em', fontWeight: '700' }],
        'h2':      ['2.25rem', { lineHeight: '1.2',  letterSpacing: '-0.02em',  fontWeight: '700' }],
        'h3':      ['1.5rem',  { lineHeight: '1.35', letterSpacing: '-0.01em',  fontWeight: '600' }],
        'h4':      ['1.25rem', { lineHeight: '1.4',  fontWeight: '600' }],
        'body-lg': ['1.125rem',{ lineHeight: '1.75' }],
        'body':    ['1rem',    { lineHeight: '1.7'  }],
        'body-sm': ['0.875rem',{ lineHeight: '1.6'  }],
        'caption': ['0.75rem', { lineHeight: '1.5', letterSpacing: '0.04em' }],
      },

      // ── Spacing ───────────────────────────────────────────
      // 8-pt grid — Tailwind's default scale already uses 4px,
      // extending with semantic spacing tokens
      spacing: {
        '18': '4.5rem',
        '22': '5.5rem',
        '26': '6.5rem',
        '30': '7.5rem',
        '34': '8.5rem',
        '38': '9.5rem',
      },

      maxWidth: {
        'container': '1280px',
        'content':   '780px',
      },

      // ── Shadows ───────────────────────────────────────────
      boxShadow: {
        'glass':      '0 4px 32px rgba(78,52,46,0.08), 0 1px 0 rgba(255,255,255,0.3) inset',
        'glass-lg':   '0 12px 48px rgba(78,52,46,0.15), 0 1px 0 rgba(255,255,255,0.25) inset',
        'glass-hover':'0 20px 64px rgba(78,52,46,0.2),  0 1px 0 rgba(255,255,255,0.3)  inset',
        'card':       '0 2px 16px rgba(78,52,46,0.06), 0 8px 32px rgba(78,52,46,0.04)',
        'card-hover': '0 8px 40px rgba(78,52,46,0.14), 0 24px 64px rgba(78,52,46,0.08)',
        'gold':       '0 0 24px rgba(200,169,106,0.35)',
        'glow-forest':'0 0 24px rgba(106,143,78,0.4)',
        'soft':       '0 1px 4px rgba(0,0,0,0.04), 0 4px 16px rgba(0,0,0,0.06)',
      },

      // ── Backdrop Blur ─────────────────────────────────────
      backdropBlur: {
        'xs':  '4px',
        'sm':  '8px',
        'md':  '12px',
        'lg':  '16px',
        'xl':  '24px',
        '2xl': '40px',
      },

      // ── Border Radius ─────────────────────────────────────
      borderRadius: {
        'sm':   '6px',
        'md':   '10px',
        'lg':   '14px',
        'xl':   '20px',
        '2xl':  '28px',
        '3xl':  '40px',
        'pill': '9999px',
      },

      // ── Animations ────────────────────────────────────────
      keyframes: {
        'fade-in': {
          '0%':   { opacity: '0', transform: 'translateY(12px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        'slide-up': {
          '0%':   { opacity: '0', transform: 'translateY(24px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        'fade-in-scale': {
          '0%':   { opacity: '0', transform: 'scale(0.95) translateY(8px)' },
          '100%': { opacity: '1', transform: 'scale(1)   translateY(0)' },
        },
        'glow-pulse': {
          '0%, 100%': { boxShadow: '0 0 0 rgba(200,169,106,0)' },
          '50%':       { boxShadow: '0 0 24px rgba(200,169,106,0.5)' },
        },
        'shimmer': {
          '0%':   { backgroundPosition: '-200% 0' },
          '100%': { backgroundPosition: '200% 0' },
        },
        'spin-slow': {
          '0%':   { transform: 'rotate(0deg)' },
          '100%': { transform: 'rotate(360deg)' },
        },
      },

      animation: {
        'fade-in':       'fade-in 0.5s ease-out both',
        'fade-in-slow':  'fade-in 0.8s ease-out both',
        'slide-up':      'slide-up 0.5s ease-out both',
        'fade-in-scale': 'fade-in-scale 0.4s cubic-bezier(0.16,1,0.3,1) both',
        'glow-pulse':    'glow-pulse 2.5s ease-in-out infinite',
        'shimmer':       'shimmer 2s linear infinite',
        'spin-slow':     'spin-slow 8s linear infinite',
      },

      // ── Transition Timing ─────────────────────────────────
      transitionTimingFunction: {
        'smooth': 'cubic-bezier(0.16, 1, 0.3, 1)',
        'bounce-soft': 'cubic-bezier(0.34, 1.56, 0.64, 1)',
      },

      transitionDuration: {
        '250': '250ms',
        '350': '350ms',
        '400': '400ms',
      },

      // ── Background Images (Malagasy geometric patterns) ───
      backgroundImage: {
        'malagasy-weave':
          "url(\"data:image/svg+xml,%3Csvg width='48' height='48' viewBox='0 0 48 48' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%234E342E' fill-opacity='0.04'%3E%3Cpath d='M0 0h24v24H0zM24 24h24v24H24z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\")",
        'organic-dots':
          "url(\"data:image/svg+xml,%3Csvg width='24' height='24' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='12' cy='12' r='2' fill='%236A8F4E' fill-opacity='0.06'/%3E%3C/svg%3E\")",
        'vanilla-grain':
          "url(\"data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E\")",
        'gradient-warm':
          'linear-gradient(135deg, #FDF9F7 0%, #F8F5F0 40%, #EDE7DC 100%)',
        'gradient-hero':
          'linear-gradient(160deg, #4E342E 0%, #7A4E33 35%, #6A8F4E 100%)',
        'gradient-glass':
          'linear-gradient(135deg, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0.05) 100%)',
      },
    },
  },

  plugins: [],
};
