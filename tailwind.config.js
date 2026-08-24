/** Tailwind build config - compiled via: npx tailwindcss@3.4.17 -c tailwind.config.js -i assets/css/app.css -o assets/app.css --minify */
module.exports = {
    content: [
        "*.php",
        "lib/*.php",
        "admin/*.php",
        "student/*.php",
        "./assets/js/*.js"
    ],
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "cf-dark": "#0f172a",
                "cf-card": "#1e293b",
                "cf-border": "#334155",
                "cf-accent": "#3b82f6"
            },
            animation: {
                "pulse-slow": "pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite",
                "blob": "blob 10s infinite"
            },
            keyframes: {
                blob: {
                    "0%": { transform: "translate(0px, 0px) scale(1)" },
                    "33%": { transform: "translate(30px, -50px) scale(1.1)" },
                    "66%": { transform: "translate(-20px, 20px) scale(0.9)" },
                    "100%": { transform: "translate(0px, 0px) scale(1)" }
                }
            }
        }
    },
    safelist: [
        "hidden",
        "flex",
        "opacity-0",
        "opacity-100",
        "opacity-10",
        "dark:opacity-5",
        "pointer-events-none",
        "pointer-events-auto"
    ]
};
