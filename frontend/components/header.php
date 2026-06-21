<meta charset="utf-8" />
<meta content="width=device-width, initial-scale=1.0" name="viewport" />

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

<link rel="stylesheet" href="/Project-Web-Programming/frontend/assets/css/style.css">
<script src="/Project-Web-Programming/frontend/assets/js/ui-helpers.js?v=20260618-2"></script>

<script id="tailwind-config">
        tailwind.config = {
                darkMode: "class",
                theme: {
                        extend: {
                                /* Ánh xạ các class Tailwind vào biến CSS trong file style.css */
                                "colors": {
                                        "primary": "var(--color-primary)",
                                        "secondary": "var(--color-secondary)",
                                        "secondary-container": "var(--color-secondary-container)",
                                        "surface": "var(--bg-surface)",
                                        "surface-container": "var(--bg-container)",
                                        "on-surface-variant": "var(--text-muted)",
                                        "outline": "var(--text-muted)",
                                        "tertiary": "var(--color-tertiary)",
                                        "on-background": "var(--text-dark)",
                                        "on-surface": "var(--text-dark)"
                                },
                                "borderRadius": {
                                        "DEFAULT": "0.25rem",
                                        "lg": "0.5rem",
                                        "xl": "0.75rem",
                                        "full": "9999px"
                                },
                                "spacing": {
                                        "container-max": "1320px",
                                        "gutter": "24px",
                                        "stack-gap": "12px",
                                        "margin-mobile": "16px",
                                        "section-gap": "48px"
                                },
                                "fontFamily": {
                                        "headline-md": ["Inter"],
                                        "body-md": ["Inter"],
                                        "headline-lg-mobile": ["Inter"],
                                        "headline-sm": ["Inter"],
                                        "label-md": ["Inter"],
                                        "body-sm": ["Inter"],
                                        "label-sm": ["Inter"],
                                        "body-lg": ["Inter"],
                                        "headline-lg": ["Inter"]
                                }
                        },
                },
        }
</script>

<style>
        /* Cấu hình bắt buộc để icon font hiển thị chuẩn xác */
        .material-symbols-outlined {
                font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
</style>
<div id="toast-container" class="fixed top-20 right-6 z-[9999] flex flex-col gap-3 max-w-sm w-full pointer-events-none"></div>