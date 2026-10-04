<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CatatDuit API — Swagger UI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css" />
    <style>
        body {
            margin: 0;
            background: #0f172a;
            color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .header-bar {
            background: #1e293b;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #334155;
        }
        .header-title {
            font-size: 18px;
            font-weight: 700;
            color: #38bdf8;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .header-links a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
            padding: 6px 12px;
            border-radius: 6px;
            background: #334155;
            transition: all 0.2s;
            margin-left: 8px;
        }
        .header-links a:hover {
            color: #ffffff;
            background: #475569;
        }
        #swagger-ui {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            margin-top: 20px;
            margin-bottom: 40px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }
        .topbar {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="header-bar">
        <div class="header-title">
            <span>💳</span> CatatDuit API — Interactive Documentation
        </div>
        <div class="header-links">
            <a href="/docs/api" title="Switch to Scramble UI">⚡ Modern Scramble UI</a>
            <a href="/docs/api.json" target="_blank" title="Raw OpenAPI Specification">📄 openapi.json</a>
        </div>
    </div>

    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js" crossorigin></script>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js" crossorigin></script>
    <script>
        window.onload = () => {
            window.ui = SwaggerUIBundle({
                url: '/docs/api.json',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: "StandaloneLayout",
                persistAuthorization: true,
                defaultModelsExpandDepth: 1,
                docExpansion: 'list'
            });
        };
    </script>
</body>
</html>
