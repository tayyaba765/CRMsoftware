<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ReqRes Users</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
            background: #f3f4f6;
        }

        .card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        h1 {
            margin-top: 0;
        }

        h2 {
            font-size: 1.1rem;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }

        button {
            background: #2563eb;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 8px;
            margin-top: 8px;
        }

        button.green {
            background: #059669;
        }

        button.purple {
            background: #7c3aed;
        }

        button:hover {
            opacity: 0.9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 14px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #f9fafb;
            font-weight: 600;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
        }

        .pagination {
            display: flex;
            gap: 6px;
            margin-top: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .pagination button {
            margin: 0;
            padding: 8px 14px;
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .pagination button.active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .success {
            color: #059669;
            font-weight: 600;
        }

        .error {
            color: #dc2626;
            font-weight: 600;
        }

        select {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
        }
    </style>
</head>

<body>

    <h1>🧪 ReqRes API → Database Integration</h1>

    <div class="card">
        <h2>1. Fetch Live Data from ReqRes API</h2>
        <div>
            <label>Per Page:</label>
            <select id="perPage">
                <option value="3">3</option>
                <option value="6" selected>6</option>
                <option value="9">9</option>
                <option value="12">12</option>
            </select>
            <button onclick="loadLive(1)">🔄 Fetch Live</button>
            <button class="green" onclick="syncToDb()">💾 Sync To Database</button>
            <button class="purple" onclick="loadSaved(1)">📂 View Saved</button>
        </div>
        <div id="liveResults"></div>
    </div>

    <div class="card" id="savedCard" style="display:none;">
        <h2>2. Users Stored In Your Database</h2>
        <div id="savedResults"></div>
    </div>

    <script>
        const API = '{{ url('/api/reqres') }}';

        // ---------- LOAD LIVE FROM API ----------
        async function loadLive(page = 1) {
            const perPage = document.getElementById('perPage').value;
            const el = document.getElementById('liveResults');
            el.innerHTML = '<p>⏳ Loading from ReqRes API...</p>';

            try {
                const res = await fetch(`${API}/users?page=${page}&per_page=${perPage}`);
                const json = await res.json();

                if (!json.status || !json.data.length) {
                    el.innerHTML = '<p class="error">❌ No users.</p>';
                    return;
                }

                el.innerHTML = renderTable(json.data, json.meta);
            } catch (err) {
                el.innerHTML = `<p class="error">❌ ${err.message}</p>`;
            }
        }

        // ---------- SYNC TO DATABASE ----------
        async function syncToDb() {
            const perPage = document.getElementById('perPage').value;
            const el = document.getElementById('liveResults');
            el.innerHTML = '<p>⏳ Syncing to database...</p>';

            try {
                const res = await fetch(`${API}/users/sync?page=1&per_page=${perPage}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                });
                const json = await res.json();

                el.innerHTML = json.status ?
                    `<p class="success">✅ ${json.message}</p>` :
                    `<p class="error">❌ ${json.message}</p>`;
            } catch (err) {
                el.innerHTML = `<p class="error">❌ ${err.message}</p>`;
            }
        }

        // ---------- LOAD SAVED FROM DB ----------
        async function loadSaved(page = 1) {
            const el = document.getElementById('savedResults');
            document.getElementById('savedCard').style.display = 'block';
            el.innerHTML = '<p>⏳ Loading from database...</p>';

            try {
                const res = await fetch(`${API}/saved?page=${page}`);
                const json = await res.json();

                if (!json.status || !json.data.length) {
                    el.innerHTML = '<p class="error">❌ No saved users yet. Click "Sync To Database" first.</p>';
                    return;
                }

                const meta = {
                    page: json.page,
                    total: json.total,
                    per_page: 6,
                    total_pages: Math.ceil(json.total / 6)
                };
                el.innerHTML = renderTable(json.data, meta);
            } catch (err) {
                el.innerHTML = `<p class="error">❌ ${err.message}</p>`;
            }
        }

        // ---------- SHARED TABLE RENDERER ----------
        function renderTable(users, meta) {
            let html = `<table>
            <thead><tr><th>ID</th><th>Avatar</th><th>Name</th><th>Email</th></tr></thead><tbody>`;

            users.forEach(u => {
                html += `<tr>
                <td>${u.id}</td>
                <td><img src="${u.avatar}" class="avatar"></td>
                <td>${u.name}</td>
                <td>${u.email}</td>
            </tr>`;
            });
            html += `</tbody></table>`;

            // Pagination
            if (meta && meta.total_pages > 1) {
                html += '<div class="pagination">';
                for (let i = 1; i <= meta.total_pages; i++) {
                    html += `<button class="${i === meta.page ? 'active' : ''}" onclick="loadLive(${i})">${i}</button>`;
                }
                html += '</div>';
            }

            return html;
        }

        // Initial load
        loadLive(1);
    </script>

</body>

</html>
