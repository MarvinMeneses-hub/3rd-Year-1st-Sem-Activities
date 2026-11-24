<style>
    .suppliers-page {
        min-height: 100vh;
        padding: 38px 24px 60px;
        background: #f5f7fb;
        color: #1d2c44;
        font-family: Inter, ui-sans-serif, system-ui, sans-serif;
    }

    .suppliers-container { max-width: 1200px; margin: 0 auto; }

    .suppliers-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 28px;
    }

    .page-eyebrow {
        color: #3475f6;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1.4px;
    }

    .suppliers-heading h1 {
        margin: 7px 0;
        color: #172842;
        font-size: 30px;
        font-weight: 800;
        letter-spacing: -.8px;
    }

    .suppliers-heading p, .muted-copy {
        margin: 0;
        color: #7b879a;
        font-size: 13px;
        line-height: 1.6;
    }

    .supplier-primary-btn, .supplier-secondary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 16px;
        border: 0;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .supplier-primary-btn { color: white; background: #3475f6; }
    .supplier-primary-btn:hover { background: #245bd7; }
    .supplier-secondary-btn { color: #40516c; background: #edf1f7; }
    .supplier-secondary-btn:hover { background: #e2e8f1; }

    .supplier-notice {
        padding: 13px 16px;
        margin-bottom: 18px;
        border-radius: 10px;
        font-size: 13px;
    }

    .supplier-notice.success { color: #176b45; background: #eaf8f0; border: 1px solid #c9ecd8; }
    .supplier-notice.error { color: #a52d38; background: #fff0f1; border: 1px solid #f5d0d3; }

    .supplier-toolbar, .supplier-panel, .supplier-form-panel, .supplier-detail-panel {
        background: white;
        border: 1px solid #e8edf5;
        border-radius: 15px;
        box-shadow: 0 5px 18px #243b5a08;
    }

    .supplier-toolbar {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 18px;
        padding: 20px;
        margin-bottom: 18px;
    }

    .supplier-toolbar h2, .supplier-panel h2, .supplier-detail-panel h2 {
        margin: 0 0 5px;
        color: #20314c;
        font-size: 15px;
        font-weight: 800;
    }

    .supplier-search {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .supplier-search input, .supplier-search select,
    .supplier-field input, .supplier-field textarea, .supplier-field select {
        width: 100%;
        padding: 11px 12px;
        color: #263750;
        background: #fff;
        border: 1px solid #dfe6f0;
        border-radius: 9px;
        outline: none;
        font: inherit;
        font-size: 13px;
    }

    .supplier-search input { width: 230px; }
    .supplier-search select { width: auto; min-width: 125px; }

    .supplier-search input:focus, .supplier-search select:focus,
    .supplier-field input:focus, .supplier-field textarea:focus, .supplier-field select:focus {
        border-color: #75a0fa;
        box-shadow: 0 0 0 3px #3475f618;
    }

    .supplier-search button {
        padding: 11px 15px;
        color: white;
        background: #3475f6;
        border: 0;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }

    .supplier-panel { overflow: hidden; }

    .supplier-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 19px 20px;
        border-bottom: 1px solid #edf0f5;
    }

    .supplier-record-count { color: #8490a4; font-size: 11px; font-weight: 700; }
    .supplier-table-wrap { overflow-x: auto; }
    .supplier-table { width: 100%; border-collapse: collapse; text-align: left; }

    .supplier-table th {
        padding: 13px 17px;
        color: #8793a7;
        background: #fafbfd;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .7px;
        white-space: nowrap;
    }

    .supplier-table td {
        padding: 15px 17px;
        color: #53627a;
        border-top: 1px solid #f0f2f6;
        font-size: 12px;
        vertical-align: middle;
    }

    .supplier-table td strong { color: #243650; font-size: 13px; }
    .supplier-table a { color: #2864df; font-weight: 700; text-decoration: none; }
    .supplier-table a:hover { text-decoration: underline; }

    .supplier-status {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 800;
    }

    .supplier-status.active { color: #177347; background: #e7f7ee; }
    .supplier-status.inactive { color: #8a5b16; background: #fff4df; }

    .supplier-actions { display: flex; align-items: center; gap: 11px; white-space: nowrap; }
    .supplier-actions form { display: inline; }

    .supplier-actions button {
        padding: 0;
        color: #d33e4b;
        background: none;
        border: 0;
        font: inherit;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .supplier-empty { padding: 38px 20px !important; color: #8793a7 !important; text-align: center; }
    .supplier-pagination { padding: 18px 20px; border-top: 1px solid #edf0f5; }

    .supplier-form-panel { padding: 24px; }
    .supplier-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 19px; }
    .supplier-field { display: grid; gap: 7px; }
    .supplier-field.full { grid-column: 1 / -1; }
    .supplier-field label { color: #40516c; font-size: 12px; font-weight: 750; }
    .supplier-field textarea { min-height: 90px; resize: vertical; }
    .supplier-field .field-error { color: #d33e4b; font-size: 11px; }
    .supplier-checkbox { display: flex; align-items: center; gap: 9px; color: #40516c; font-size: 13px; }
    .supplier-checkbox input { width: 16px; height: 16px; accent-color: #3475f6; }
    .supplier-form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }

    .supplier-detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .supplier-detail-panel { padding: 20px; }
    .supplier-detail-item { padding: 12px 0; border-bottom: 1px solid #f0f2f6; }
    .supplier-detail-item:last-child { border-bottom: 0; }
    .supplier-detail-item small { display: block; margin-bottom: 5px; color: #8793a7; font-size: 10px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; }
    .supplier-detail-item strong { color: #293b56; font-size: 13px; overflow-wrap: anywhere; }

    @media (max-width: 700px) {
        .suppliers-page { padding: 25px 14px 45px; }
        .suppliers-heading { align-items: flex-start; flex-direction: column; }
        .suppliers-heading h1 { font-size: 26px; }
        .supplier-toolbar { align-items: stretch; flex-direction: column; }
        .supplier-search { align-items: stretch; flex-direction: column; }
        .supplier-search input, .supplier-search select { width: 100%; }
        .supplier-form-grid, .supplier-detail-grid { grid-template-columns: 1fr; }
        .supplier-field.full { grid-column: auto; }
        .supplier-form-panel { padding: 18px; }
        .supplier-form-actions { flex-direction: column-reverse; }
        .supplier-form-actions a, .supplier-form-actions button { width: 100%; }
    }
</style>
