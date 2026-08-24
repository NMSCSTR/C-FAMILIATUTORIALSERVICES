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
