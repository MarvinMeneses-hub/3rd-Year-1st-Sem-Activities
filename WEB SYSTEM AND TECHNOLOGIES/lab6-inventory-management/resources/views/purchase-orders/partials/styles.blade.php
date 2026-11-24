<style>
.po-page {
    padding: 35px 20px;
    color: #172033;
}
.po-container {
    max-width: 1200px;
    margin: auto;
}
.po-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 28px;
}
.po-heading h1 {
    font-size: 30px;
    font-weight: 750;
    margin: 5px 0;
}
.po-heading p, .po-muted {
    color: #64748b;
    margin: 0;
}
.po-eyebrow {
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
}
.po-card {
    background: white;
    border: 1px solid #e5eaf1;
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 3px 12px rgba(15, 23, 42, .035);
}
.po-toolbar, .po-actions, .po-form-actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
.po-toolbar {
    justify-content: space-between;
    margin-bottom: 18px;
}
.po-input, .po-select, .po-textarea {
    width: 100%;
    border: 1px solid #dbe2ea;
    border-radius: 8px;
    padding: 10px 12px;
    background: white;
    color: #172033;
}
.po-textarea { min-height: 95px; resize: vertical; }
.po-search { display: flex; gap: 8px; flex-wrap: wrap; }
.po-search .po-input { width: 240px; }
.po-btn {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    border: 0;
    border-radius: 8px;
    padding: 10px 15px;
    background: #1e40af;
    color: white;
    text-decoration: none;
    font-weight: 650;
    cursor: pointer;
    font-size: 14px;
}
.po-btn:hover { opacity: .9; }
.po-btn.secondary { background: #e8edf5; color: #263449; }
.po-btn.success { background: #15803d; }
.po-btn.danger { background: #b91c1c; }
.po-btn.warning { background: #b45309; }
.po-table-wrap { overflow-x: auto; }
.po-table { width: 100%; border-collapse: collapse; }
.po-table th, .po-table td {
    text-align: left;
    padding: 14px 12px;
    border-bottom: 1px solid #edf0f4;
    white-space: nowrap;
}
.po-table th {
    color: #64748b;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.po-table td { font-size: 14px; }
.po-table a { color: #1d4ed8; text-decoration: none; font-weight: 650; }
.po-status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    background: #e2e8f0;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    text-transform: capitalize;
}
.po-status.pending { background: #fef3c7; color: #92400e; }
.po-status.approved { background: #dbeafe; color: #1e40af; }
.po-status.sent { background: #ede9fe; color: #6d28d9; }
.po-status.received { background: #dcfce7; color: #166534; }
.po-status.rejected, .po-status.cancelled { background: #fee2e2; color: #991b1b; }
.po-status.draft { background: #e2e8f0; color: #334155; }
.po-alert { padding: 13px 16px; border-radius: 9px; margin-bottom: 18px; }
.po-alert.success { background: #dcfce7; color: #166534; }
.po-alert.error { background: #fee2e2; color: #991b1b; }
.po-field { margin-bottom: 18px; }
.po-field label { display: block; font-weight: 650; margin-bottom: 7px; font-size: 14px; }
.po-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
.po-items-heading { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 15px; }
.po-item-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 12px;
    align-items: end;
    padding: 14px;
    background: #f8fafc;
    border: 1px solid #e5eaf1;
    border-radius: 10px;
    margin-bottom: 12px;
}
.po-total {
    display: flex;
    justify-content: flex-end;
    gap: 30px;
    padding: 15px 0;
    font-size: 17px;
    font-weight: 750;
}
.po-detail-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.po-detail-label { color: #64748b; font-size: 12px; margin-bottom: 5px; }
.po-detail-value { font-weight: 700; overflow-wrap: anywhere; }
.po-empty { padding: 35px; text-align: center; color: #64748b; }
.po-pagination { margin-top: 18px; }
.po-actions form { display: inline-flex; }
@media(max-width: 700px) {
    .po-heading { align-items: flex-start; flex-direction: column; }
    .po-grid, .po-detail-grid { grid-template-columns: 1fr; }
    .po-item-row { grid-template-columns: 1fr 1fr; }
    .po-search, .po-search .po-input { width: 100%; }
    .po-card { padding: 16px; }
}
</style>
