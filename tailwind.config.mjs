/** @type {import('tailwindcss').Config} */
export default {
  content: ['./src/**/*.{astro,html,js,jsx,md,mdx,ts,tsx}'],
  theme: {
    extend: {
      colors: {
        teal: {
          DEFAULT: '#00B4A6',
          deep: '#08938C',
          bright: '#2ECDC1',
        },
        mid: '#7B5EA7',
        purple: {
          DEFAULT: '#9B59B6',
          mid: '#7B5EA7',
        },
        magenta: {
          DEFAULT: '#E91E8C',
          hot: '#FF2D9B',
          light: '#FF5CB8',
        },
        dark: {
          DEFAULT: '#0B1820',
          card: '#111F2A',
          deep: '#0E232F',
          border: 'rgba(255,255,255,0.06)',
        },
        light: {
          bg: '#F4F6F8',
          card: '#FFFFFF',
          border: '#E4E9EE',
        },
        ink: {
          DEFAULT: '#1C2A36',
          muted: '#6B7D8D',
        },
        gold: '#F4C542',
      },
      fontFamily: {
        display: ['Fraunces', 'ui-serif', 'Georgia', 'serif'],
        sans: ['"DM Sans"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
      },
      backgroundImage: {
        'gradient-brand': 'linear-gradient(135deg,#00B4A6 0%,#7B5EA7 50%,#E91E8C 100%)',
        'gradient-brand-h': 'linear-gradient(90deg,#00B4A6 0%,#7B5EA7 50%,#E91E8C 100%)',
        'gradient-subtle': 'linear-gradient(135deg,rgba(0,180,166,0.08) 0%,rgba(233,30,140,0.08) 100%)',
        'gradient-dark': 'linear-gradient(180deg,#0B1820 0%,#0E232F 100%)',
      },
      borderRadius: {
        brand: '16px',
        'brand-sm': '10px',
      },
      boxShadow: {
        brand: '0 4px 20px rgba(233,30,140,0.3)',
        'brand-lg': '0 8px 32px rgba(233,30,140,0.4)',
        'brand-soft': '0 4px 16px rgba(233,30,140,0.25)',
        card: '0 2px 12px rgba(0,0,0,0.04)',
        'card-hover': '0 8px 24px rgba(0,0,0,0.08)',
        'float-dark': '0 40px 80px rgba(0,0,0,0.4)',
      },
      animation: {
        float: 'float 4s ease-in-out infinite',
        'fade-up': 'fadeUp 0.7s cubic-bezier(0.16,1,0.3,1) both',
      },
      keyframes: {
        float: {
          '0%,100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-10px)' },
        },
        fadeUp: {
          from: { opacity: 0, transform: 'translateY(40px)' },
          to: { opacity: 1, transform: 'translateY(0)' },
        },
      },
    },
  },
  plugins: [],
};
