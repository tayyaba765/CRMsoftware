<!-- ===================================================================== -->
<!-- CORK MODULAR THEME SYSTEM (Tailwind CSS, Bootstrap 5, & DataTables)   -->
<!-- Provides unified Light and Dark Theme tokens, components, and scripts -->
<!-- ===================================================================== -->

<!-- Theme CSS Tokens & Shared Component Styles -->
<style id="cork-theme-styles">
    /* ------------------------------------------------------------- */
    /* 1. Theme Color Tokens & Root Variables                        */
    /* ------------------------------------------------------------- */
    :root {
        --cork-brand: #5b3fd4;
        --cork-brand-hover: #4a2ec3;
        --cork-brand-subtle: rgba(91, 63, 212, 0.08);
        --cork-brand-glow: rgba(91, 63, 212, 0.2);

        --cork-bg-main: #ffffff;
        --cork-bg-card: #ffffff;
        --cork-bg-subtle: #f9fafb;
        --cork-bg-input: #ffffff;

        --cork-border: #e5e7eb;
        --cork-border-subtle: #f3f4f6;

        --cork-text-main: #111827;
        --cork-text-secondary: #4b5563;
        --cork-text-muted: #6b7280;
    }

    html.dark,
    [data-bs-theme="dark"] {
        --cork-bg-main: #111827;
        --cork-bg-card: #1f2937;
        --cork-bg-subtle: #1e293b;
        --cork-bg-input: #0f172a;

        --cork-border: #374151;
        --cork-border-subtle: #1f2937;

        --cork-text-main: #f9fafb;
        --cork-text-secondary: #d1d5db;
        --cork-text-muted: #9ca3af;
    }

    /* ------------------------------------------------------------- */
    /* 2. Global Brand & Typography Helpers                          */
    /* ------------------------------------------------------------- */
    .text-brand {
        color: #5b3fd4 !important;
    }

    .bg-brand {
        background-color: #5b3fd4 !important;
        color: #ffffff !important;
    }

    .bg-brand-subtle {
        background-color: rgba(91, 63, 212, 0.1) !important;
    }

    .border-brand {
        border-color: #5b3fd4 !important;
    }

    .btn-brand {
        background-color: #5b3fd4 !important;
        border-color: #5b3fd4 !important;
        color: #ffffff !important;
        transition: all 0.18s ease-in-out;
    }

    .btn-brand:hover,
    .btn-brand:focus,
    .btn-brand:active {
        background-color: #4a2ec3 !important;
        border-color: #4a2ec3 !important;
        color: #ffffff !important;
    }

    .btn-outline-brand {
        color: #5b3fd4 !important;
        border-color: #5b3fd4 !important;
        background-color: transparent !important;
        transition: all 0.18s ease-in-out;
    }

    .btn-outline-brand:hover,
    .btn-outline-brand:focus,
    .btn-outline-brand:active {
        background-color: #5b3fd4 !important;
        color: #ffffff !important;
    }

    .extra-small {
        font-size: 0.75rem;
    }

    .hover-scale {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-scale:hover {
        transform: scale(1.08);
    }

    /* ------------------------------------------------------------- */
    /* 3. Universal Tailwind Card & Surface Components              */
    /* ------------------------------------------------------------- */
    .card,
    .theme-card {
        background-color: var(--cork-bg-card);
        border: 1px solid var(--cork-border);
        border-radius: 0.75rem;
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }

    html.dark .card,
    html.dark .theme-card {
        background-color: #1f2937 !important;
        border-color: #374151 !important;
        color: #f9fafb !important;
    }

    /* ------------------------------------------------------------- */
    /* 4. Universal DataTables Styling (Light & Dark Theme)          */
    /* ------------------------------------------------------------- */
    .dataTables_wrapper {
        padding: 0.75rem 0;
        font-size: 0.875rem;
        color: var(--cork-text-muted);
        position: relative;
    }

    .dataTables_wrapper .dataTables_length {
        float: left;
        margin: 0.5rem 1rem;
    }

    .dataTables_wrapper .dataTables_filter {
        float: right;
        text-align: right;
        margin: 0.5rem 1rem;
    }

    .dataTables_wrapper .dataTables_info {
        float: left;
        margin: 0.75rem 1rem;
        clear: both;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .dataTables_wrapper .dataTables_paginate {
        float: right;
        text-align: right;
        margin: 0.75rem 1rem;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .dataTables_wrapper::after {
        content: "";
        display: table;
        clear: both;
    }

    .dataTables_wrapper label {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-weight: 500;
        color: #6b7280;
    }

    html.dark .dataTables_wrapper label,
    html.dark .dataTables_wrapper .dataTables_info,
    html.dark .dataTables_wrapper .dataTables_paginate {
        color: #9ca3af !important;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.35rem 0.65rem;
        margin-left: 0.35rem;
        outline: none;
        background-color: #ffffff;
        color: #111827;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .dataTables_wrapper .dataTables_filter input:focus,
    .dataTables_wrapper .dataTables_length select:focus {
        border-color: #5b3fd4;
        box-shadow: 0 0 0 3px rgba(91, 63, 212, 0.15);
    }

    html.dark .dataTables_wrapper .dataTables_filter input,
    html.dark .dataTables_wrapper .dataTables_length select {
        background-color: #111827 !important;
        border-color: #4b5563 !important;
        color: #f3f4f6 !important;
    }

    html.dark .dataTables_wrapper .dataTables_filter input::placeholder {
        color: #6b7280 !important;
    }

    html.dark .dataTables_wrapper .dataTables_length select option {
        background-color: #1f2937 !important;
        color: #f3f4f6 !important;
    }

    @media (max-width: 640px) {

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            float: none !important;
            text-align: left !important;
            margin: 0.5rem 0.75rem !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            margin-left: 0 !important;
            margin-top: 0.25rem !important;
            width: 100% !important;
        }
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.3rem 0.7rem;
        margin-left: 0.25rem;
        border-radius: 0.375rem;
        cursor: pointer;
        color: #6b7280 !important;
        border: 1px solid transparent;
        background-color: transparent !important;
        transition: all 0.15s ease;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: rgba(91, 63, 212, 0.08) !important;
        color: #5b3fd4 !important;
        border-color: transparent;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #5b3fd4 !important;
        color: #ffffff !important;
        border: 1px solid #5b3fd4 !important;
        font-weight: 600;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    html.dark .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: #9ca3af !important;
        background-color: transparent !important;
        border: 1px solid transparent !important;
    }

    html.dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background-color: rgba(91, 63, 212, 0.25) !important;
        color: #ffffff !important;
        border-color: rgba(91, 63, 212, 0.4) !important;
    }

    html.dark .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    html.dark .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #5b3fd4 !important;
        color: #ffffff !important;
        border: 1px solid #5b3fd4 !important;
        font-weight: 600;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
    }

    html.dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    html.dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #4b5563 !important;
        background-color: transparent !important;
        border-color: transparent !important;
        cursor: not-allowed;
    }

    /* Table Base */
    table.dataTable,
    table[id$="Table"],
    #orgsTable,
    #profilesTable {
        width: 100% !important;
        border-collapse: collapse !important;
        color: inherit;
        background-color: transparent !important;
    }

    html.dark table.dataTable,
    html.dark table[id$="Table"],
    html.dark #orgsTable,
    html.dark #profilesTable {
        color: #e5e7eb !important;
        background-color: transparent !important;
    }

    /* Thead */
    table.dataTable thead,
    table[id$="Table"] thead,
    #orgsTable thead,
    #profilesTable thead {
        background-color: #f9fafb;
    }

    html.dark table.dataTable thead,
    html.dark table[id$="Table"] thead,
    html.dark #orgsTable thead,
    html.dark #profilesTable thead {
        background-color: rgba(30, 41, 59, 0.85) !important;
    }

    table.dataTable thead th,
    table[id$="Table"] thead th,
    #orgsTable thead th,
    #profilesTable thead th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        background-color: transparent;
        padding: 0.75rem 0.75rem;
        border-bottom: 1px solid #e5e7eb;
    }

    html.dark table.dataTable thead th,
    html.dark table[id$="Table"] thead th,
    html.dark #orgsTable thead th,
    html.dark #profilesTable thead th {
        color: #9ca3af !important;
        background-color: transparent !important;
        border-bottom: 1px solid #374151 !important;
    }

    /* Tbody & Rows */
    table.dataTable tbody,
    table[id$="Table"] tbody,
    #orgsTable tbody,
    #profilesTable tbody {
        background-color: transparent !important;
    }

    table.dataTable tbody tr,
    table[id$="Table"] tbody tr,
    #orgsTable tbody tr,
    #profilesTable tbody tr {
        border-top: 1px solid #e5e7eb;
        background-color: transparent !important;
        transition: background-color 0.15s ease;
    }

    table.dataTable tbody tr.odd,
    table.dataTable tbody tr.even,
    table[id$="Table"] tbody tr.odd,
    table[id$="Table"] tbody tr.even,
    #orgsTable tbody tr.odd,
    #orgsTable tbody tr.even,
    #profilesTable tbody tr.odd,
    #profilesTable tbody tr.even {
        background-color: transparent !important;
    }

    table.dataTable tbody tr:hover,
    table[id$="Table"] tbody tr:hover,
    #orgsTable tbody tr:hover,
    #profilesTable tbody tr:hover {
        background-color: rgba(91, 63, 212, 0.04) !important;
    }

    html.dark table.dataTable tbody tr,
    html.dark table[id$="Table"] tbody tr,
    html.dark #orgsTable tbody tr,
    html.dark #profilesTable tbody tr {
        border-top: 1px solid #374151 !important;
        border-bottom: 1px solid #374151 !important;
        background-color: transparent !important;
        color: #e5e7eb !important;
    }

    /* Elegant Subtle Striping in Dark Mode */
    html.dark table.dataTable tbody tr.odd,
    html.dark table[id$="Table"] tbody tr.odd,
    html.dark #orgsTable tbody tr.odd,
    html.dark #profilesTable tbody tr.odd {
        background-color: rgba(255, 255, 255, 0.02) !important;
    }

    html.dark table.dataTable tbody tr.even,
    html.dark table[id$="Table"] tbody tr.even,
    html.dark #orgsTable tbody tr.even,
    html.dark #profilesTable tbody tr.even {
        background-color: transparent !important;
    }

    html.dark table.dataTable tbody tr:hover,
    html.dark table[id$="Table"] tbody tr:hover,
    html.dark #orgsTable tbody tr:hover,
    html.dark #profilesTable tbody tr:hover {
        background-color: rgba(91, 63, 212, 0.15) !important;
    }

    /* Cells (TD) */
    table.dataTable tbody td,
    table[id$="Table"] tbody td,
    #orgsTable tbody td,
    #profilesTable tbody td {
        padding: 0.75rem 0.75rem;
        vertical-align: middle;
        background-color: transparent !important;
    }

    html.dark table.dataTable tbody td,
    html.dark table[id$="Table"] tbody td,
    html.dark #orgsTable tbody td,
    html.dark #profilesTable tbody td {
        color: #e5e7eb !important;
        background-color: transparent !important;
        border-color: #374151 !important;
    }

    /* Target AJAX injected text inside dark mode tables */
    html.dark table.dataTable .text-gray-900,
    html.dark table[id$="Table"] .text-gray-900,
    html.dark #orgsTable .text-gray-900,
    html.dark #profilesTable .text-gray-900 {
        color: #f9fafb !important;
    }

    html.dark table.dataTable .text-gray-800,
    html.dark table[id$="Table"] .text-gray-800,
    html.dark #orgsTable .text-gray-800,
    html.dark #profilesTable .text-gray-800 {
        color: #f3f4f6 !important;
    }

    html.dark table.dataTable .text-gray-700,
    html.dark table[id$="Table"] .text-gray-700,
    html.dark #orgsTable .text-gray-700,
    html.dark #profilesTable .text-gray-700 {
        color: #e5e7eb !important;
    }

    html.dark table.dataTable .text-gray-600,
    html.dark table[id$="Table"] .text-gray-600,
    html.dark #orgsTable .text-gray-600,
    html.dark #profilesTable .text-gray-600 {
        color: #d1d5db !important;
    }

    html.dark table.dataTable .text-gray-500,
    html.dark table[id$="Table"] .text-gray-500,
    html.dark #orgsTable .text-gray-500,
    html.dark #profilesTable .text-gray-500 {
        color: #9ca3af !important;
    }

    html.dark table.dataTable .text-gray-400,
    html.dark table[id$="Table"] .text-gray-400,
    html.dark #orgsTable .text-gray-400,
    html.dark #profilesTable .text-gray-400 {
        color: #9ca3af !important;
    }

    /* Links inside dark tables */
    html.dark table.dataTable a.text-brand,
    html.dark #orgsTable a.text-brand,
    html.dark #profilesTable a.text-brand {
        color: #a5b4fc !important;
    }

    html.dark table.dataTable a.text-brand:hover,
    html.dark #orgsTable a.text-brand:hover,
    html.dark #profilesTable a.text-brand:hover {
        color: #c7d2fe !important;
    }

    /* Badges & Logos in Dark Mode */
    html.dark table.dataTable img,
    html.dark #orgsTable img,
    html.dark #profilesTable img {
        background-color: #1f2937 !important;
        border-color: #374151 !important;
    }

    /* Universal Badge Palette for Dark Mode */
    html.dark table.dataTable .bg-green-100,
    html.dark #orgsTable .bg-green-100,
    html.dark #profilesTable .bg-green-100 {
        background-color: rgba(22, 101, 52, 0.4) !important;
        border: 1px solid rgba(74, 222, 128, 0.3) !important;
    }

    html.dark table.dataTable .text-green-800,
    html.dark #orgsTable .text-green-800,
    html.dark #profilesTable .text-green-800 {
        color: #86efac !important;
    }

    html.dark table.dataTable .bg-blue-100,
    html.dark #orgsTable .bg-blue-100,
    html.dark #profilesTable .bg-blue-100 {
        background-color: rgba(30, 64, 175, 0.4) !important;
        border: 1px solid rgba(96, 165, 250, 0.3) !important;
    }

    html.dark table.dataTable .text-blue-800,
    html.dark #orgsTable .text-blue-800,
    html.dark #profilesTable .text-blue-800 {
        color: #93c5fd !important;
    }

    html.dark table.dataTable .bg-indigo-100,
    html.dark #orgsTable .bg-indigo-100,
    html.dark #profilesTable .bg-indigo-100 {
        background-color: rgba(55, 48, 163, 0.4) !important;
        border: 1px solid rgba(129, 140, 248, 0.3) !important;
    }

    html.dark table.dataTable .text-indigo-800,
    html.dark #orgsTable .text-indigo-800,
    html.dark #profilesTable .text-indigo-800 {
        color: #a5b4fc !important;
    }

    html.dark table.dataTable .bg-pink-100,
    html.dark #orgsTable .bg-pink-100,
    html.dark #profilesTable .bg-pink-100 {
        background-color: rgba(157, 23, 77, 0.4) !important;
        border: 1px solid rgba(244, 114, 182, 0.3) !important;
    }

    html.dark table.dataTable .text-pink-800,
    html.dark #orgsTable .text-pink-800,
    html.dark #profilesTable .text-pink-800 {
        color: #f472b6 !important;
    }

    html.dark table.dataTable .bg-amber-100,
    html.dark #orgsTable .bg-amber-100,
    html.dark #profilesTable .bg-amber-100 {
        background-color: rgba(146, 64, 14, 0.4) !important;
        border: 1px solid rgba(251, 191, 36, 0.3) !important;
    }

    html.dark table.dataTable .text-amber-800,
    html.dark #orgsTable .text-amber-800,
    html.dark #profilesTable .text-amber-800 {
        color: #fcd34d !important;
    }

    html.dark table.dataTable .bg-purple-100,
    html.dark #orgsTable .bg-purple-100,
    html.dark #profilesTable .bg-purple-100 {
        background-color: rgba(107, 33, 168, 0.4) !important;
        border: 1px solid rgba(192, 132, 252, 0.3) !important;
    }

    html.dark table.dataTable .text-purple-800,
    html.dark #orgsTable .text-purple-800,
    html.dark #profilesTable .text-purple-800 {
        color: #d8b4fe !important;
    }

    html.dark table.dataTable .bg-sky-100,
    html.dark #orgsTable .bg-sky-100,
    html.dark #profilesTable .bg-sky-100 {
        background-color: rgba(14, 116, 144, 0.4) !important;
        border: 1px solid rgba(56, 189, 248, 0.3) !important;
    }

    html.dark table.dataTable .text-sky-800,
    html.dark #orgsTable .text-sky-800,
    html.dark #profilesTable .text-sky-800 {
        color: #7dd3fc !important;
    }

    html.dark table.dataTable .bg-teal-100,
    html.dark #orgsTable .bg-teal-100,
    html.dark #profilesTable .bg-teal-100 {
        background-color: rgba(19, 78, 74, 0.4) !important;
        border: 1px solid rgba(45, 212, 191, 0.3) !important;
    }

    html.dark table.dataTable .text-teal-800,
    html.dark #orgsTable .text-teal-800,
    html.dark #profilesTable .text-teal-800 {
        color: #5eead4 !important;
    }

    html.dark table.dataTable .bg-violet-100,
    html.dark #orgsTable .bg-violet-100,
    html.dark #profilesTable .bg-violet-100 {
        background-color: rgba(91, 33, 182, 0.4) !important;
        border: 1px solid rgba(167, 139, 250, 0.3) !important;
    }

    html.dark table.dataTable .text-violet-800,
    html.dark #orgsTable .text-violet-800,
    html.dark #profilesTable .text-violet-800 {
        color: #c4b5fd !important;
    }

    html.dark table.dataTable .bg-gray-100,
    html.dark #orgsTable .bg-gray-100,
    html.dark #profilesTable .bg-gray-100 {
        background-color: rgba(55, 65, 81, 0.6) !important;
        border: 1px solid rgba(75, 85, 99, 0.6) !important;
        color: #e5e7eb !important;
    }

    /* Tier Badges (Tier 1-4) */
    html.dark table.dataTable .bg-emerald-50,
    html.dark #orgsTable .bg-emerald-50 {
        background-color: rgba(6, 78, 59, 0.45) !important;
        border: 1px solid rgba(16, 185, 129, 0.4) !important;
    }

    html.dark table.dataTable .text-emerald-700,
    html.dark #orgsTable .text-emerald-700 {
        color: #6ee7b7 !important;
    }

    html.dark table.dataTable .bg-indigo-50,
    html.dark #orgsTable .bg-indigo-50 {
        background-color: rgba(49, 46, 129, 0.45) !important;
        border: 1px solid rgba(99, 102, 241, 0.4) !important;
    }

    html.dark table.dataTable .text-indigo-700,
    html.dark #orgsTable .text-indigo-700 {
        color: #a5b4fc !important;
    }

    html.dark table.dataTable .bg-amber-50,
    html.dark #orgsTable .bg-amber-50 {
        background-color: rgba(120, 53, 15, 0.45) !important;
        border: 1px solid rgba(245, 158, 11, 0.4) !important;
    }

    html.dark table.dataTable .text-amber-700,
    html.dark #orgsTable .text-amber-700 {
        color: #fde047 !important;
    }

    html.dark table.dataTable .bg-gray-50,
    html.dark #orgsTable .bg-gray-50 {
        background-color: rgba(55, 65, 81, 0.5) !important;
        border: 1px solid #4b5563 !important;
    }

    /* Status badge text colors */
    html.dark table.dataTable .text-amber-600,
    html.dark #orgsTable .text-amber-600,
    html.dark #profilesTable .text-amber-600 {
        color: #fbbf24 !important;
    }

    html.dark table.dataTable .text-emerald-600,
    html.dark #orgsTable .text-emerald-600,
    html.dark #profilesTable .text-emerald-600 {
        color: #34d399 !important;
    }

    html.dark table.dataTable .text-purple-600,
    html.dark #orgsTable .text-purple-600,
    html.dark #profilesTable .text-purple-600 {
        color: #c084fc !important;
    }

    /* Assigned User Badge in Dark Mode */
    html.dark table.dataTable [class*="bg-brand\/10"],
    html.dark #orgsTable [class*="bg-brand\/10"],
    html.dark #profilesTable [class*="bg-brand\/10"] {
        background-color: rgba(91, 63, 212, 0.25) !important;
        color: #c4b5fd !important;
    }

    /* Action Buttons in DataTables */
    html.dark table.dataTable .view_btn,
    html.dark table.dataTable .view_org_btn,
    html.dark #orgsTable .view_org_btn,
    html.dark #profilesTable .view_btn {
        background-color: #374151 !important;
        color: #d1d5db !important;
        border: 1px solid #4b5563 !important;
    }

    html.dark table.dataTable .view_btn:hover,
    html.dark table.dataTable .view_org_btn:hover,
    html.dark #orgsTable .view_org_btn:hover,
    html.dark #profilesTable .view_btn:hover {
        background-color: #4b5563 !important;
        color: #a5b4fc !important;
        border-color: #6366f1 !important;
    }

    /* Force Table Card Container to be dark in dark mode */
    html.dark #slot-content .bg-white,
    html.dark .dataTables_wrapper .bg-white {
        background-color: #1f2937 !important;
        border-color: #374151 !important;
        color: #f9fafb !important;
    }

    /* Coordinate Pin Badge in Dark Mode */
    html.dark #coordsBadge,
    html.dark #orgCoordsBadge {
        background-color: #0f172a !important;
        color: #e2e8f0 !important;
        border-color: #334155 !important;
    }

    /* Processing spinner */
    .dataTables_wrapper .dataTables_processing {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 10;
        background: rgba(255, 255, 255, 0.95);
        color: #111827;
        border: 1px solid #e5e7eb;
    }

    html.dark .dataTables_wrapper .dataTables_processing {
        background: rgba(31, 41, 55, 0.95) !important;
        color: #f9fafb !important;
        border: 1px solid #374151 !important;
    }

    /* Empty state */
    html.dark .dataTables_wrapper .dataTables_empty {
        color: #9ca3af !important;
        background-color: transparent !important;
    }

    /* Sortable Header Indicators */
    table.dataTable thead th.sorting,
    table.dataTable thead th.sorting_asc,
    table.dataTable thead th.sorting_desc,
    table[id$="Table"] thead th.sorting,
    table[id$="Table"] thead th.sorting_asc,
    table[id$="Table"] thead th.sorting_desc {
        cursor: pointer;
        position: relative;
        padding-right: 1.5rem !important;
        user-select: none;
    }

    table.dataTable thead th.sorting::after,
    table[id$="Table"] thead th.sorting::after {
        content: '\2195';
        position: absolute;
        right: 0.5rem;
        opacity: 0.35;
        font-size: 0.75rem;
    }

    table.dataTable thead th.sorting_asc::after,
    table[id$="Table"] thead th.sorting_asc::after {
        content: '\2191';
        position: absolute;
        right: 0.5rem;
        opacity: 1;
        color: #5b3fd4;
        font-size: 0.75rem;
    }

    table.dataTable thead th.sorting_desc::after,
    table[id$="Table"] thead th.sorting_desc::after {
        content: '\2193';
        position: absolute;
        right: 0.5rem;
        opacity: 1;
        color: #5b3fd4;
        font-size: 0.75rem;
    }

    html.dark table.dataTable thead th.sorting::after,
    html.dark table[id$="Table"] thead th.sorting::after {
        color: #9ca3af !important;
        opacity: 0.5;
    }

    html.dark table.dataTable thead th.sorting_asc::after,
    html.dark table[id$="Table"] thead th.sorting_asc::after,
    html.dark table.dataTable thead th.sorting_desc::after,
    html.dark table[id$="Table"] thead th.sorting_desc::after {
        color: #818cf8 !important;
        opacity: 1;
    }

    html.dark table.dataTable thead th.sorting:hover,
    html.dark table.dataTable thead th.sorting_asc:hover,
    html.dark table.dataTable thead th.sorting_desc:hover,
    html.dark table[id$="Table"] thead th.sorting:hover,
    html.dark table[id$="Table"] thead th.sorting_asc:hover,
    html.dark table[id$="Table"] thead th.sorting_desc:hover {
        background-color: rgba(91, 63, 212, 0.15) !important;
        color: #ffffff !important;
    }

    /* DataTables No-Footer Border Normalize */
    table.dataTable.no-footer,
    table[id$="Table"].no-footer,
    #orgsTable.no-footer,
    #profilesTable.no-footer {
        border-bottom: 1px solid #e5e7eb !important;
    }

    html.dark table.dataTable.no-footer,
    html.dark table[id$="Table"].no-footer,
    html.dark #orgsTable.no-footer,
    html.dark #profilesTable.no-footer {
        border-bottom: 1px solid #374151 !important;
    }

    /* ------------------------------------------------------------- */
    /* 5. Universal Bootstrap 5 & Tailwind Modal Dark/Light Theme    */
    /* ------------------------------------------------------------- */
    .modal-dialog-scrollable {
        height: calc(100vh - 3.5rem) !important;
        max-height: calc(100vh - 3.5rem) !important;
        display: flex !important;
    }

    .modal-dialog-scrollable .modal-content {
        height: 100% !important;
        max-height: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        border-radius: 1.25rem !important;
    }

    .modal-dialog-scrollable .modal-header {
        flex-shrink: 0 !important;
    }

    .modal-dialog-scrollable .modal-footer {
        flex-shrink: 0 !important;
        border-top: 1px solid rgba(0, 0, 0, 0.08) !important;
    }

    .modal-dialog-scrollable .modal-body {
        flex: 1 1 0px !important;
        min-height: 0 !important;
        overflow-y: scroll !important;
        scrollbar-gutter: stable;
        scrollbar-width: thin;
        scrollbar-color: #5b3fd4 rgba(91, 63, 212, 0.1);
    }

    .modal-dialog-scrollable .modal-body::-webkit-scrollbar {
        width: 7px;
        display: block !important;
    }

    .modal-dialog-scrollable .modal-body::-webkit-scrollbar-track {
        background: rgba(91, 63, 212, 0.05);
        border-radius: 8px;
    }

    .modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb {
        background: #5b3fd4;
        border-radius: 8px;
    }

    /* Clean Form Focus Ring */
    .form-control:focus,
    .form-select:focus {
        border-color: #5b3fd4 !important;
        box-shadow: 0 0 0 0.2rem rgba(91, 63, 212, 0.18) !important;
    }

    .input-group-text {
        background-color: rgba(91, 63, 212, 0.05);
        color: #5b3fd4;
        border-color: #dee2e6;
    }

    /* Modal Dark Theme Overrides */
    html.dark .modal-content,
    html.dark [data-bs-theme="dark"] .modal-content {
        background-color: #1e293b !important;
        color: #f1f5f9 !important;
        border-color: #334155 !important;
    }

    html.dark .modal-header,
    html.dark [data-bs-theme="dark"] .modal-header {
        background-color: #0f172a !important;
        border-bottom: 1px solid #334155 !important;
    }

    html.dark .modal-footer,
    html.dark [data-bs-theme="dark"] .modal-footer {
        background-color: #0f172a !important;
        border-top: 1px solid #334155 !important;
    }

    html.dark .modal-body,
    html.dark [data-bs-theme="dark"] .modal-body {
        background-color: #1e293b !important;
        color: #f1f5f9 !important;
    }

    html.dark .bg-light-subtle,
    html.dark [data-bs-theme="dark"] .bg-light-subtle {
        background-color: rgba(15, 23, 42, 0.6) !important;
        border-color: #334155 !important;
    }

    html.dark .text-dark,
    html.dark [data-bs-theme="dark"] .text-dark {
        color: #f8fafc !important;
    }

    html.dark .text-secondary,
    html.dark [data-bs-theme="dark"] .text-secondary {
        color: #94a3b8 !important;
    }

    html.dark .form-control,
    html.dark .form-select,
    html.dark [data-bs-theme="dark"] .form-control,
    html.dark [data-bs-theme="dark"] .form-select {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }

    html.dark .input-group-text,
    html.dark [data-bs-theme="dark"] .input-group-text {
        background-color: rgba(91, 63, 212, 0.2) !important;
        color: #c4b5fd !important;
        border-color: #334155 !important;
    }

    html.dark .border-bottom,
    html.dark .border-top,
    html.dark .border {
        border-color: #334155 !important;
    }

    html.dark .btn-light,
    html.dark [data-bs-theme="dark"] .btn-light {
        background-color: #334155 !important;
        border-color: #475569 !important;
        color: #f1f5f9 !important;
    }

    html.dark .btn-close,
    html.dark [data-bs-theme="dark"] .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    /* Social icons & buttons dark mode contrast */
    html.dark .bi-facebook {
        color: #60a5fa !important;
    }

    html.dark .bi-instagram {
        color: #f472b6 !important;
    }

    html.dark .bi-linkedin {
        color: #38bdf8 !important;
    }

    html.dark .bi-threads {
        color: #f8fafc !important;
    }

    html.dark .btn-outline-dark {
        color: #e2e8f0 !important;
        border-color: #475569 !important;
    }

    html.dark .btn-outline-dark:hover {
        background-color: #334155 !important;
        color: #ffffff !important;
    }

    /* ------------------------------------------------------------- */
    /* 6. Leaflet Map Containers Dark/Light Adaptations              */
    /* ------------------------------------------------------------- */
    #orgMapViewBox,
    #viewOrgMapBox,
    #mapViewBox,
    #viewMapBox {
        position: relative;
        z-index: 1;
        transition: border-color 0.2s ease;
    }

    html.dark #orgMapViewBox,
    html.dark #viewOrgMapBox,
    html.dark #mapViewBox,
    html.dark #viewMapBox {
        border-color: #334155 !important;
    }

    html.dark .leaflet-tile-pane {
        filter: brightness(0.65) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3) brightness(0.7);
    }

    html.dark .leaflet-container {
        background-color: #0f172a !important;
    }

    html.dark .leaflet-bar a {
        background-color: #1e293b !important;
        color: #f1f5f9 !important;
        border-color: #334155 !important;
    }

    html.dark .leaflet-bar a:hover {
        background-color: #334155 !important;
        color: #ffffff !important;
    }
</style>

<!-- Modular Theme Synchronization Script -->
<script>
    (function(window, document) {
        'use strict';

        var CorkTheme = {
            /**
             * Check if dark mode is currently active
             */
            isDark: function() {
                var stored = localStorage.getItem('cork-theme');
                if (stored === 'dark') return true;
                if (stored === 'light') return false;
                return document.documentElement.classList.contains('dark') ||
                    (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
            },

            /**
             * Return current theme string ('dark' | 'light')
             */
            getTheme: function() {
                return this.isDark() ? 'dark' : 'light';
            },

            /**
             * Set theme mode and synchronize DOM & storage
             */
            setTheme: function(theme) {
                var isDark = (theme === 'dark');
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
                localStorage.setItem('cork-theme', isDark ? 'dark' : 'light');

                this.syncAllModals(isDark);

                window.dispatchEvent(new CustomEvent('cork:theme', {
                    detail: {
                        dark: isDark
                    }
                }));
            },

            /**
             * Toggle between dark and light themes
             */
            toggle: function() {
                this.setTheme(this.isDark() ? 'light' : 'dark');
            },

            /**
             * Synchronize modal or container element
             */
            sync: function(el) {
                var theme = this.getTheme();
                if (el) {
                    if (typeof el === 'string') {
                        el = document.querySelector(el);
                    }
                    if (el && el.setAttribute) {
                        el.setAttribute('data-bs-theme', theme);
                    }
                } else {
                    document.documentElement.setAttribute('data-bs-theme', theme);
                }
            },

            /**
             * Synchronize all Bootstrap modals currently present in DOM
             */
            syncAllModals: function(isDark) {
                var theme = (isDark !== undefined ? isDark : this.isDark()) ? 'dark' : 'light';
                var modals = document.querySelectorAll('.modal');
                modals.forEach(function(m) {
                    m.setAttribute('data-bs-theme', theme);
                });
            }
        };

        // Initial sync on script load
        var initialDark = CorkTheme.isDark();
        document.documentElement.classList.toggle('dark', initialDark);
        document.documentElement.setAttribute('data-bs-theme', initialDark ? 'dark' : 'light');

        // Listen for navbar theme toggle event
        window.addEventListener('cork:theme', function(e) {
            var dark = (e.detail && typeof e.detail.dark === 'boolean') ? e.detail.dark : CorkTheme
                .isDark();
            CorkTheme.syncAllModals(dark);
        });

        // Automatically synchronize any Bootstrap modal when shown
        document.addEventListener('show.bs.modal', function(e) {
            if (e.target) {
                e.target.setAttribute('data-bs-theme', CorkTheme.getTheme());
            }
        });

        // Expose global API
        window.CorkTheme = CorkTheme;
    })(window, document);
</script>
