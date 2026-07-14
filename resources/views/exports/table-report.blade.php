<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapport {{ $title }}</title>
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #0f172a;
            background: #f8fafc;
        }

        .page {
            padding: 28px;
        }

        .header {
            border: 1px solid #dbeafe;
            background: #eff6ff;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 20px;
        }

        .eyebrow {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #2563eb;
            margin: 0;
        }

        h1 {
            margin: 10px 0 0;
            font-size: 28px;
            line-height: 1.15;
        }

        .sub {
            margin: 10px 0 0;
            font-size: 13px;
            color: #475569;
            line-height: 1.6;
        }

        .meta {
            margin-top: 18px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .chip {
            border: 1px solid #bfdbfe;
            background: #ffffff;
            border-radius: 999px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 700;
            color: #1e3a8a;
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #ffffff;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            text-align: left;
            font-size: 11px;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #2563eb;
            background: #eff6ff;
            padding: 14px 16px;
            border-bottom: 1px solid #dbeafe;
        }

        tbody td {
            font-size: 12px;
            padding: 13px 16px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            vertical-align: top;
        }

        tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .empty {
            padding: 24px 16px;
            color: #64748b;
            text-align: center;
        }

        .footer {
            margin-top: 16px;
            font-size: 11px;
            color: #64748b;
            text-align: right;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .page {
                padding: 0;
            }

            .header {
                border-radius: 0;
                margin-bottom: 16px;
            }
        }
    </style>
    @if (!empty($preview))
        <script>
            window.addEventListener('load', () => {
                window.print();
            });
        </script>
    @endif
</head>
<body>
    <div class="page">
        <div class="header">
            <p class="eyebrow">Rapport {{ $title }}</p>
            <h1>{{ $title }}</h1>
            <p class="sub">{{ $subtitle }}</p>

            <div class="meta">
                <span class="chip">Portée : {{ $scopeLabel }}</span>
                <span class="chip">Généré le : {{ $generatedAt }}</span>
            </div>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td class="empty" colspan="{{ count($columns) }}">
                                Aucun enregistrement pour cette portée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer">
            Généré par {{ config('app.name') }}
        </div>
    </div>
</body>
</html>
