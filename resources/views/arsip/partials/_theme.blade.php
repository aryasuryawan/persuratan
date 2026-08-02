<style>
    .simpel-detail{
        --primary:#3f8f8e;
        --primary-dark:#2c6b6a;
        --primary-light:var(--tblr-primary-lt);
        --warning:var(--tblr-warning);
        --warning-light:var(--tblr-warning-lt);
        --danger:var(--tblr-danger);
        --danger-light:var(--tblr-danger-lt);
        --success:var(--tblr-success);
        --success-light:var(--tblr-success-lt);
        --ink:var(--tblr-body-color);
        --muted:var(--tblr-secondary-color);
        --faint:var(--tblr-secondary);
        --border:var(--tblr-border-color);
        --bg:var(--tblr-body-bg);
        --card:var(--tblr-bg-surface);
        --radius:8px;
    }
    .simpel-detail{color:var(--ink); font-size:14px; line-height:1.5;}
    .simpel-detail a{color:var(--primary-dark); text-decoration:none;}
    .simpel-detail a:hover{text-decoration:underline;}

    .simpel-detail .page{margin:0 auto; padding:8px 0 40px;}
    .simpel-detail .breadcrumb{font-size:12.5px; color:var(--faint); margin-bottom:6px;}
    .simpel-detail .breadcrumb a{color:var(--faint);}
    .simpel-detail .page-head{display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:20px; flex-wrap:wrap;}
    .simpel-detail .page-head h1{font-size:22px; margin:0 0 8px; font-weight:700;}
    .simpel-detail .badge-row{display:flex; gap:8px; align-items:center; flex-wrap:wrap;}
    .simpel-detail .badge{
        display:inline-flex; align-items:center; gap:5px;
        font-size:12px; font-weight:600; padding:4px 10px; border-radius:20px;
    }
    .simpel-detail .badge svg{width:12px; height:12px;}
    .simpel-detail .badge-dark{background:#1d2939; color:#fff;}
    .simpel-detail .badge-success{background:var(--success-light); color:var(--success);}
    .simpel-detail .badge-warning{background:var(--warning-light); color:var(--warning);}
    .simpel-detail .badge-danger{background:var(--danger-light); color:var(--danger);}
    .simpel-detail .badge-muted{background:var(--tblr-secondary-bg); color:var(--muted);}
    .simpel-detail .btn{
        border:1px solid var(--border); background:var(--card); color:var(--ink);
        padding:8px 14px; border-radius:6px; font-size:13.5px; font-weight:600;
        display:inline-flex; align-items:center; gap:7px; cursor:pointer; text-decoration:none;
    }
    .simpel-detail .btn svg{width:15px; height:15px;}
    .simpel-detail .btn-outline-primary{color:var(--primary-dark); border-color:var(--primary);}
    .simpel-detail .btn-outline-primary:hover{background:var(--primary-light);}
    .simpel-detail .btn-outline-warning{color:var(--warning); border-color:var(--warning);}
    .simpel-detail .btn-primary{background:var(--primary); border-color:var(--primary); color:#fff;}
    .simpel-detail .btn-primary:hover{background:var(--primary-dark);}
    .simpel-detail .btn-danger{background:var(--danger); border-color:var(--danger); color:#fff;}
    .simpel-detail .btn-danger:hover{background:var(--danger); filter:brightness(.92);}
    .simpel-detail .btn-sm{padding:6px 11px; font-size:12.5px;}
    .simpel-detail .btn-block{width:100%; justify-content:center;}
    .simpel-detail .btn-ghost{border-color:transparent; background:transparent; padding:6px 8px;}

    .simpel-detail .grid{display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start;}
    @media (max-width:900px){ .simpel-detail .grid{grid-template-columns:1fr;} }

    .simpel-detail .card{
        background:var(--card); border:1px solid var(--border); border-radius:var(--radius);
        margin-bottom:20px; overflow:hidden;
    }
    .simpel-detail .card-header{
        padding:14px 20px; border-bottom:1px solid var(--border);
        display:flex; align-items:center; justify-content:space-between;
    }
    .simpel-detail .card-header h3{margin:0; font-size:15px; font-weight:700;}
    .simpel-detail .card-body{padding:20px;}
    .simpel-detail .muted-count{font-size:12px; color:var(--faint); font-weight:500;}

    .simpel-detail .datagrid{display:grid; grid-template-columns:1fr 1fr; gap:16px 28px;}
    .simpel-detail .datagrid-item{padding-bottom:2px;}
    .simpel-detail .datagrid-item.span-2{grid-column:1 / -1;}
    .simpel-detail .datagrid-title{font-size:12px; color:var(--faint); font-weight:600; text-transform:uppercase; letter-spacing:.3px; margin-bottom:3px;}
    .simpel-detail .datagrid-value{font-size:14px; color:var(--ink);}
    .simpel-detail .attachment{
        display:flex; align-items:center; gap:10px; background:var(--tblr-bg-surface-tertiary); border:1px solid var(--border);
        border-radius:6px; padding:8px 12px; margin-top:4px; text-decoration:none;
    }
    .simpel-detail .attachment svg{width:18px; height:18px; color:var(--danger); flex-shrink:0;}
    .simpel-detail .attachment .name{font-size:13px; font-weight:600; color:var(--ink);}
    .simpel-detail .attachment .size{font-size:11.5px; color:var(--faint);}
    .simpel-detail .attachment{cursor:pointer;}
    .simpel-detail .attachment:hover{border-color:var(--primary); text-decoration:none;}

    .simpel-detail .disp-item{border:1px solid var(--border); border-radius:8px; margin-bottom:14px;}
    .simpel-detail .disp-item:last-child{margin-bottom:0;}
    .simpel-detail .disp-main{padding:16px 18px;}
    .simpel-detail .disp-top{display:flex; align-items:flex-start; justify-content:space-between; gap:12px;}
    .simpel-detail .disp-who{display:flex; gap:10px;}
    .simpel-detail .avatar{
        width:32px; height:32px; border-radius:50%; background:var(--primary);
        color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0;
    }
    .simpel-detail .disp-who .avatar{width:34px; height:34px; font-size:12.5px;}
    .simpel-detail .disp-name{font-weight:700; font-size:13.5px;}
    .simpel-detail .disp-time{font-size:11.5px; color:var(--faint); margin-top:1px;}
    .simpel-detail .disp-revisi{font-size:11px;}
    .simpel-detail .disp-msg{font-size:13.5px; margin:10px 0 8px; color:var(--ink);}
    .simpel-detail .disp-meta{display:flex; gap:16px; flex-wrap:wrap; font-size:12.5px; color:var(--muted); margin-bottom:10px;}
    .simpel-detail .disp-meta b{color:var(--ink);}
    .simpel-detail .chip-deadline{
        display:inline-flex; align-items:center; gap:5px; font-size:11.5px; font-weight:700;
        padding:2px 9px; border-radius:20px;
    }
    .simpel-detail .disp-actions{display:flex; gap:8px; margin-top:6px;}
    .simpel-detail .icon-edit{width:14px; height:14px; color:var(--faint); cursor:pointer;}
    .simpel-detail .icon-edit:hover{color:var(--primary-dark);}

    .simpel-detail .followup{
        margin:0 18px 16px 44px; padding:12px 14px; background:var(--tblr-bg-surface-tertiary); border-radius:8px;
        border-left:3px solid var(--primary);
    }
    .simpel-detail .followup-top{display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;}
    .simpel-detail .followup-who{display:flex; align-items:center; gap:8px; font-size:12.5px; font-weight:700;}
    .simpel-detail .followup-time{font-size:11px; color:var(--faint);}
    .simpel-detail .followup-text{font-size:13px; color:var(--ink); margin-bottom:8px;}
    .simpel-detail .status-tag{
        display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:700;
        padding:3px 9px; border-radius:5px; margin-bottom:8px;
    }

    .simpel-detail .tabs{display:flex; gap:4px; border-bottom:1px solid var(--border); margin-bottom:4px;}
    .simpel-detail .tab{padding:10px 14px; font-size:13px; font-weight:600; color:var(--muted); cursor:pointer; border-bottom:2px solid transparent; display:flex; align-items:center; gap:6px; text-decoration:none; margin-bottom:-1px;}
    .simpel-detail .tab.active{color:var(--primary-dark); border-color:var(--primary);}
    .simpel-detail .tab .count{background:var(--tblr-secondary-bg); color:var(--muted); font-size:10.5px; padding:1px 6px; border-radius:10px;}
    .simpel-detail .tab.active .count{background:var(--primary-light); color:var(--primary-dark);}

    .simpel-detail .history-toggle{
        display:flex; align-items:center; justify-content:space-between; padding:14px 20px;
        cursor:pointer; font-size:13px; font-weight:600; color:var(--primary-dark); border:0; background:none; width:100%;
    }
    .simpel-detail .history-toggle svg{width:15px; height:15px; transition:transform .15s;}
    .simpel-detail .history-toggle svg.rot{transform:rotate(180deg);}
    .simpel-detail .history-body{padding:0 20px 20px; display:none;}
    .simpel-detail .history-body.open{display:block;}

    .simpel-detail .tl{position:relative; padding-left:26px;}
    .simpel-detail .tl::before{content:""; position:absolute; left:8px; top:4px; bottom:4px; width:1px; background:var(--border);}
    .simpel-detail .tl-item{position:relative; padding-bottom:18px;}
    .simpel-detail .tl-item:last-child{padding-bottom:0;}
    .simpel-detail .tl-dot{
        position:absolute; left:-26px; top:2px; width:16px; height:16px; border-radius:50%;
        background:var(--card); border:2px solid var(--border); display:flex; align-items:center; justify-content:center;
    }
    .simpel-detail .tl-dot.done{border-color:var(--success); background:var(--success-light);}
    .simpel-detail .tl-dot svg{width:9px; height:9px; color:var(--success);}
    .simpel-detail .tl-card{border:1px solid var(--border); border-radius:7px; padding:12px 14px; background:var(--card);}
    .simpel-detail .tl-head{display:flex; justify-content:space-between; align-items:baseline; margin-bottom:4px;}
    .simpel-detail .tl-title{font-size:13px; font-weight:700;}
    .simpel-detail .tl-when{font-size:11.5px; color:var(--faint);}
    .simpel-detail .tl-body{font-size:12.5px; color:var(--muted);}
    .simpel-detail .tl-body b{color:var(--ink);}

    .simpel-detail .side{position:sticky; top:20px;}
    .simpel-detail .summary-row{display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid var(--border); font-size:13px;}
    .simpel-detail .summary-row:last-child{border-bottom:none;}
    .simpel-detail .summary-row .k{color:var(--muted);}
    .simpel-detail .summary-row .v{font-weight:600; text-align:right;}
    .simpel-detail .divider{height:1px; background:var(--border); margin:12px 0;}

    .simpel-detail .table th{font-size:12px; color:var(--faint); font-weight:600; text-transform:uppercase; letter-spacing:.3px; border-bottom:1px solid var(--border);}
    .simpel-detail .table td{color:var(--ink); border-bottom:1px solid var(--border);}
    .simpel-detail .table tr:last-child td{border-bottom:none;}
    .simpel-detail .table-hover tbody tr:hover{background:var(--tblr-bg-surface-tertiary);}
    .simpel-detail .filter-bar{display:flex; flex-wrap:wrap; gap:10px; align-items:center;}
</style>

<!-- Quill WYSIWYG via CDN -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill on textareas with class "wysiwyg"
    document.querySelectorAll('textarea.wysiwyg').forEach(function(textarea) {
        if (textarea.dataset.quillInitialized) return;
        
        // Create container div for Quill (insert before textarea)
        var container = document.createElement('div');
        container.id = textarea.id + '-quill';
        textarea.parentNode.insertBefore(container, textarea);
        
        // Hide original textarea (keep for form submit)
        textarea.style.display = 'none';
        
        // Initialize Quill on container
        var editor = new Quill(container, {
            theme: 'snow',
            placeholder: textarea.placeholder || 'Tulis di sini...',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });
        
        // Load existing content from textarea into Quill
        if (textarea.value) {
            try {
                editor.root.innerHTML = textarea.value;
            } catch (e) {
                editor.clipboard.dangerouslyPasteHTML(textarea.value);
            }
        }
        
        // Sync Quill -> textarea on text change
        editor.on('text-change', function() {
            textarea.value = editor.root.innerHTML;
        });
        
        // Sync on form submit (backup)
        var form = textarea.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                textarea.value = editor.root.innerHTML;
            });
        }
        
        textarea.dataset.quillInitialized = 'true';
    });
});
</script>
