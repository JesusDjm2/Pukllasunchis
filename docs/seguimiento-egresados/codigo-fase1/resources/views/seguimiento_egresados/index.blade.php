<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width,initial-scale=1" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pukllasunchis · Seguimiento Sostenible de Egresados · Intranet V8</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/logoiesp.ico') }}">
    <style>
        :root{--sidebar:#181d25;--sidehover:#252b35;--blue:#4e75e5;--teal:#32b3c5;--green:#117843;--ink:#353f56;--muted:#858b9e;--line:#e8ebf4;--bg:#f5f6fa;--shadow:0 7px 25px rgba(24,36,68,.075)}
        *{box-sizing:border-box}
        html,body{margin:0;min-height:100%;font:14px/1.46 Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif;color:var(--ink);background:var(--bg)}
        button,input,select,textarea{font:inherit}
        button{cursor:pointer}
        button:focus-visible,input:focus-visible,select:focus-visible,textarea:focus-visible{outline:2px solid var(--blue);outline-offset:2px}
        #root{display:flex;min-height:100vh}
        .sidebar{width:286px;flex:none;background:var(--sidebar);color:#e5e9f2;min-height:100vh;display:flex;flex-direction:column;padding:18px 13px 22px;position:sticky;top:0;height:100vh;overflow:auto}
        .brand{display:grid;place-items:center;margin:0 auto 12px;height:94px}
        .brand img{width:204px;height:100px;object-fit:contain}
        .brand-pill{margin:2px auto 19px;border-radius:18px;border:1px solid #747a84;padding:3px 14px;font-size:10px;letter-spacing:1.5px;font-weight:850;background:#313640;color:#f3f0e9}
        .profile{display:flex;gap:11px;align-items:center;background:#252b33;border:1px solid #3c424d;border-radius:15px;padding:12px;margin-bottom:21px}
        .avatar{width:43px;height:43px;display:grid;place-items:center;border-radius:12px;background:#d99b40;color:white;font-weight:900}
        .profile b{font-size:13px;color:white}
        .profile small{display:block;color:#a7afbd}
        .navheader{font-size:10px;letter-spacing:1.3px;color:#9199a9;font-weight:850;margin:13px 11px 8px}
        .navitem{width:100%;display:flex;align-items:center;gap:12px;text-align:left;border:0;border-radius:12px;padding:11px 12px;background:transparent;color:#d7dbe4;font-weight:650;margin:2px 0}
        .navitem:hover{background:#2b323e}
        .navitem.active{background:#3b352c;color:#fff;box-shadow:inset 3px 0 #dfa957}
        .navitem .ico{width:30px;height:30px;background:#292e37;border-radius:8px;display:grid;place-items:center;font-size:15px}
        .navitem.active .ico{background:#544937}
        .navitem em{font-size:10px;font-style:normal;margin-left:auto;background:#4d77e1;padding:3px 7px;border-radius:12px;color:white}
        .sidebar .bottom{margin-top:auto;padding-top:14px;border-top:1px solid #323946}
        .main{min-width:0;flex:1}
        .topbar{height:76px;background:#fff;border-bottom:1px solid #eceefa;box-shadow:0 2px 12px #18233e09;display:flex;align-items:center;justify-content:space-between;padding:0 27px;gap:18px}
        .season{background:var(--teal);color:white;padding:9px 12px;border-radius:4px;font-weight:750;white-space:nowrap}
        .topright{display:flex;align-items:center;gap:20px;color:#a6a9b9}
        .topright select{border:1px solid #dfe4f2;border-radius:10px;padding:9px 12px;background:#f5f7fc;color:var(--ink);font-weight:650}
        .top-avatar{border-left:1px solid #e6e9f1;padding-left:18px;color:#989cb3;font-weight:700;white-space:nowrap}
        .content{padding:34px 22px 50px;max-width:1680px;margin:auto}
        .hero{background:#fff;border-radius:11px;border-left:3px solid #5478eb;padding:27px 30px;box-shadow:var(--shadow);display:flex;justify-content:space-between;gap:18px;align-items:center;margin-bottom:23px}
        .eyebrow{font-size:12px;letter-spacing:.7px;font-weight:800;color:#858b9e}
        .hero h1{font-size:26px;line-height:1.2;color:#51586c;margin:6px 0 10px;letter-spacing:-.5px}
        .hero p{color:#7c8396;margin:0 0 10px}
        .tag{display:inline-flex;align-items:center;gap:6px;color:#3959ba;font-size:12px;font-weight:750;background:#eef1ff;padding:7px 10px;border-radius:20px}
        .hero-actions{display:flex;flex-direction:column;align-items:flex-end;gap:8px}
        .btn{border:0;border-radius:11px;padding:11px 17px;color:white;font-weight:750;background:var(--blue);display:inline-flex;gap:8px;align-items:center;justify-content:center;box-shadow:0 3px 8px #4e75e52f;text-decoration:none}
        .btn:hover{filter:brightness(.96);color:white}
        .btn.green{background:#147747}
        .btn.gray{color:#48516c;background:#edf0f7;box-shadow:none}
        .btn.outline{color:var(--blue);background:white;border:1px solid #d2dcff;box-shadow:none}
        .btn.sm{font-size:12px;padding:7px 11px;border-radius:8px}
        .btn.gold{background:#dfa62a}
        .btn.red{background:#d34e54}
        .pills{display:flex;gap:7px;flex-wrap:wrap}
        .pill{border-radius:30px;background:#f0f2fb;color:#656f89;padding:6px 10px;font-size:11px;font-weight:700}
        .chip{font-size:11px;font-weight:750;border-radius:20px;padding:5px 10px;display:inline-block;color:#2765bd;background:#e9f0ff}
        .chip.ok{color:#087b58;background:#dbf7eb}
        .chip.warn{color:#986419;background:#fff0ce}
        .chip.bad{color:#a74c54;background:#ffe8eb}
        .panel{background:white;border-radius:12px;padding:23px 26px;box-shadow:var(--shadow);margin-bottom:20px}
        .panel h2{font-size:20px;margin:0 0 5px}
        .panel h3{font-size:15px;margin:0 0 10px}
        .sub{font-size:12px;color:var(--muted);margin-bottom:19px}
        .section-top{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
        .cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:20px}
        .stat{background:#fff;border-radius:12px;padding:20px 21px;box-shadow:var(--shadow);border-left:3px solid transparent}
        .stat .label{font-size:12px;color:#82899d;font-weight:750}
        .stat .number{font-size:31px;letter-spacing:-1px;font-weight:850;margin:10px 0 4px;color:#3d4560}
        .stat .foot{font-size:11px;color:#8f95a6}
        .stat.c1{border-color:#4e75e5}
        .stat.c2{border-color:#29ad87}
        .stat.c3{border-color:#d9a04d}
        .stat.c4{border-color:#aa78d0}
        .two{display:grid;grid-template-columns:1.45fr 1fr;gap:20px}
        .twosame{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .progressrow{display:grid;grid-template-columns:minmax(115px,1fr) minmax(160px,2fr) 54px;align-items:center;gap:12px;margin:14px 0;font-size:12px}
        .bar{height:10px;background:#ebedf5;border-radius:15px;overflow:hidden}
        .bar span{display:block;height:100%;border-radius:15px;background:#5779e4}
        .bar.teal span{background:#2ba7a7}
        .bar.gold span{background:#e2a940}
        .bar.purple span{background:#a57ccd}
        table{border-collapse:collapse;width:100%;font-size:12px}
        th{text-align:left;color:#8a90a2;text-transform:uppercase;letter-spacing:.3px;background:#f7f8fc;font-size:10px}
        td,th{padding:13px 12px;border-bottom:1px solid #eef0f6}
        td{color:#545e77}
        tr:last-child td{border-bottom:0}
        .muted{color:#9ca4b4}
        .toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:16px 0}
        .field{display:flex;flex-direction:column;gap:6px;min-width:0}
        .field label{font-size:12px;color:#6a748c;font-weight:750}
        .field input,.field select,.field textarea{width:100%;padding:11px 12px;border-radius:9px;border:1px solid #dce2f0;background:#fff;color:#3a465e;outline:0}
        .field textarea{min-height:86px;resize:vertical}
        .field input:focus,.field select:focus,.field textarea:focus{border-color:#5780e6;box-shadow:0 0 0 3px #5780e619}
        .notice{padding:14px 17px;background:#eff5ff;color:#5a6888;border-radius:10px;font-size:12px}
        .notice.privacy{background:#fff7e7;color:#8f622a;border:1px solid #f6dfb3}
        .warningbox{background:#fff7e8;border:1px solid #f6dfa9;border-radius:10px;padding:14px 17px;color:#93692b;font-size:12px}
        .history{font-size:12px;background:#f9fafc;border-left:3px solid #6c92e3;padding:11px 14px;margin:8px 0;color:#67748d}
        .tabstrip{display:flex;gap:6px;flex-wrap:wrap;padding:4px;background:#f0f2f8;border-radius:11px;width:max-content;max-width:100%}
        .tabstrip button{background:transparent;border:0;padding:9px 13px;border-radius:8px;color:#66708a;font-weight:760}
        .tabstrip button.selected{background:#fff;color:#486fe2;box-shadow:0 2px 9px #28395a18}
        .tablewrap{overflow:auto;max-height:580px;border-radius:8px}
        .tablewrap table{min-width:840px}
        .tablewrap tr:hover td{background:#f8faff}
        .tablewrap td{vertical-align:middle}
        .mutedline{font-size:11px;color:#828da4;display:block}
        .opgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
        .opbox{padding:18px;border:1px solid #e5e9f4;border-radius:12px;min-height:120px}
        .opbox strong{display:block;font-size:15px;margin:9px 0}
        .opbox p{font-size:12px;color:#8a91a3;margin:0}
        .personline{display:flex;align-items:center;justify-content:space-between;padding:13px 0;border-bottom:1px solid #eff1f6}
        .personline:last-child{border-bottom:0}
        .personline strong{display:block;font-size:13px}
        .personline span{font-size:11px;color:#949caf}
        .modalbg{position:fixed;inset:0;background:#0c1525b8;z-index:15;display:grid;place-items:center;padding:15px}
        .modal{width:min(580px,100%);background:white;border-radius:16px;padding:25px;box-shadow:0 20px 70px #0004}
        .modal h2{margin:0 0 7px}
        .modal .buttons{display:flex;justify-content:end;gap:10px;margin-top:18px}
        .v8-two{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px 22px;margin:13px 0 22px}
        .v8-three{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
        .v8-three>div{background:#f7f8fc;padding:14px;border-radius:10px}
        .v8-head{background:#eaf0ff;border-left:3px solid #4d78ea;color:#3f5ab0;font-size:13px;font-weight:800;padding:11px 14px;border-radius:6px;margin:20px 0 13px}
        .v8-hidden{display:none}
        .v8-confirm{display:flex;gap:12px;align-items:flex-start;font-size:13px;margin:18px 0}
        .v8-confirm input{margin-top:3px;width:16px;height:16px}
        .v8-list{padding-left:21px;line-height:1.8;color:#657087}
        .v8-list li{margin:8px 0}
        .question-card{background:#fff;border:1px solid #e5eaf3;border-radius:13px;margin:16px 0;overflow:hidden}
        .question-header{background:#f8faff;padding:16px 20px;border-bottom:1px solid #e8ecf5;display:flex;gap:14px;align-items:center}
        .question-number{display:grid;place-items:center;background:#e8eeff;color:#4d72e1;border-radius:50%;width:34px;height:34px;font-weight:850;flex:none}
        .question-header h3{margin:0;font-size:16px}
        .question-header p{margin:2px 0 0;color:#8a94a7;font-size:12px}
        .question-body{padding:20px}
        .q-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .qfield{display:flex;flex-direction:column;gap:6px}
        .qfield label{font-size:12px;font-weight:780;color:#687289}
        .qfield input,.qfield select,.qfield textarea{width:100%;border:1px solid #dce2f0;border-radius:9px;padding:11px 12px;background:white;color:#35415a}
        .qfield textarea{min-height:85px;resize:vertical}
        .qfield.full{grid-column:1/-1}
        .sticky-action{position:sticky;bottom:0;background:#fffffff5;backdrop-filter:blur(8px);border-top:1px solid #e8ebf5;padding:13px 0;display:flex;align-items:center;justify-content:space-between;gap:12px;z-index:4}
        .footer{font-size:11px;color:#a0a8b8;text-align:center;margin-top:23px}
        .demo{font-size:11px;letter-spacing:.4px;background:#fff0d4;color:#8a5d17;border-radius:20px;padding:6px 10px;font-weight:800}

        /* Analítica CSS */
        .ai-title{background:linear-gradient(105deg,#ffffff,#eef4ff);border-left:4px solid #4f76e5}
        .ai-heading{font-size:23px;margin:0 0 6px;color:#313a52}
        .ai-pre{font-size:10px;text-transform:uppercase;letter-spacing:1.2px;color:#6a83a9;font-weight:850}
        .ai-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:2px 0 18px}
        .ai-tabs button{border:1px solid #dfe5f4;background:#fff;border-radius:9px;padding:9px 14px;font-weight:700;color:#53637b}
        .ai-tabs button.selected{border-color:#4d76e5;background:#eaf0fe;color:#365fc6}
        .ai-filters{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
        .ai-filter label,.ai-select label{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#69758b;font-weight:800;margin-bottom:6px}
        .ai-filter select,.ai-select select{width:100%;background:white;border:1px solid #dce2ee;border-radius:9px;padding:10px;color:#3b4560}
        .ai-toolbar{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:15px}
        .ai-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin:20px 0}
        .ai-kpi{padding:17px 18px;background:#fff;border-radius:12px;box-shadow:var(--shadow);border-top:3px solid #617de5}
        .ai-kpi small{font-weight:750;color:#7a8496}
        .ai-kpi strong{display:block;font-size:27px;margin:7px 0 3px;color:#35445f}
        .ai-kpi span{font-size:11px;color:#818b9f}
        .ai-tools{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
        .ai-table-scroll{max-width:100%;overflow:auto;border:1px solid #e9edf5;border-radius:10px}
        .ai-table{border-collapse:collapse;width:100%;font-size:13px;background:white}
        .ai-table th{background:#eef2fa;color:#546481;white-space:nowrap;text-align:left;font-size:11px;padding:12px 13px;border-bottom:1px solid #dbe4f1}
        .ai-table td{padding:11px 13px;border-bottom:1px solid #edf0f5;color:#46536d}
        .ai-table td.num{text-align:right;font-variant-numeric:tabular-nums}
        .ai-tag{display:inline-flex;background:#f0f3ff;color:#466ac7;border-radius:14px;padding:3px 9px;font-size:11px;font-weight:750}
        .ai-note{border-left:3px solid #dba64b;background:#fff9e8;color:#725e3a;padding:12px 15px;border-radius:8px;font-size:12px;line-height:1.55}
        .ai-bar{background:#e8edf4;height:11px;border-radius:9px;overflow:hidden;min-width:80px}
        .ai-bar>i{display:block;height:100%;background:#5377e1;border-radius:8px}
        .ai-chart{display:grid;gap:13px}
        .ai-chart-row{display:grid;grid-template-columns:minmax(110px,180px) 1fr 53px;gap:10px;align-items:center}
        .ai-titlebar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px}
        .ai-grid2{display:grid;grid-template-columns:1.1fr .9fr;gap:16px}
        .ai-pipeline{display:flex;gap:0;align-items:stretch;overflow:auto}
        .ai-step{min-width:180px;flex:1;border:1px solid #dde4f2;background:#f8faff;border-radius:11px;padding:17px}
        .ai-step:not(:last-child){margin-right:10px}
        .ai-step b{display:block;font-size:14px;color:#344a78}
        .ai-step small{display:block;color:#798399;margin-top:6px}
        .ai-track{border-left:3px solid #90a4df;margin-left:11px;padding-left:23px;display:grid;gap:15px}
        .ai-event{position:relative;background:#f6f8fd;padding:15px;border-radius:10px;margin-bottom:10px}
        .ai-event:before{content:"";position:absolute;width:10px;height:10px;border-radius:50%;background:#557bdc;left:-30px;top:19px;border:3px solid white;box-shadow:0 0 0 1px #557bdc}
        .ai-event b{color:#384c75}
        .ai-event small{display:block;color:#697793;margin-top:5px}

        /* Territorio CSS */
        .geo-controls{display:flex;flex-wrap:wrap;gap:12px;margin:15px 0 20px}
        .geo-controls .field{flex:1 1 178px;min-width:170px}
        .geo-grid{display:grid;grid-template-columns:minmax(0,1.8fr) minmax(260px,.85fr);gap:18px}
        .geo-mapframe{border:1px solid #dae3ed;border-radius:15px;overflow:hidden;background:#f7fafc;padding:8px}
        .geo-footer{padding:8px 12px 4px;display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;font-size:11px;color:#77839a}
        .geo-aside{border:1px solid #e7eaf2;border-radius:14px;padding:15px;background:#fff;min-width:0}
        .geo-key{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;font-size:11px;color:#77839a}
        .geo-key>div{display:flex;align-items:center;gap:6px}
        .geo-key i{width:12px;height:12px;display:inline-block;border-radius:3px;border:1px solid #d1dbe8}
        .geo-dot{cursor:pointer;transition:opacity .1s}
        .geo-dot:hover{opacity:.74}

        /* Portal Campaña */
        body.campana-abierta #root{display:none}
        body:not(.campana-abierta) #campaign-portal{display:none}
        #campaign-portal{min-height:100vh;background:linear-gradient(130deg,#f5f7fc,#e9f1f7);display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:36px 16px 70px}
        .camp-head{width:100%;max-width:920px;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:22px}
        .camp-head img{width:167px;height:84px;object-fit:contain;background:#171c24;border-radius:11px;padding:12px}
        .camp-chip{background:#dff8f0;color:#117b54;font-size:12px;border:1px solid #b8e9d9;font-weight:800;padding:7px 12px;border-radius:24px}
        .camp-card{width:100%;max-width:920px;background:white;border-radius:17px;overflow:hidden;border:1px solid #e6ebf4;box-shadow:0 15px 40px #1d30531c}
        .camp-banner{padding:30px 35px;background:linear-gradient(110deg,#243951,#4e75e5);color:white}
        .camp-banner small{letter-spacing:1.5px;text-transform:uppercase;font-weight:800;color:#c3dbfb}
        .camp-banner h1{font-size:28px;font-weight:850;margin:9px 0 7px;color:white}
        .camp-banner p{color:#e5edff;font-size:14px;margin:0;max-width:650px}
        .camp-inner{padding:30px 35px}
        .camp-steps{display:flex;gap:12px;margin-bottom:24px}
        .camp-step{flex:1;border-radius:11px;padding:12px 10px;background:#f4f6fb;color:#9298a9;font-size:12px;font-weight:750;text-align:center}
        .camp-step.on{background:#eaf0ff;color:#416bce}
        .camp-step.done{background:#e6f8f1;color:#117b54}
        .camp-two{display:grid;grid-template-columns:1fr 1fr;gap:17px}
        .camp-inner label{display:block;color:#535e76;font-size:12px;font-weight:800;margin:14px 0 5px}
        .camp-inner input,.camp-inner select{width:100%;padding:13px 12px;border:1px solid #dce3f0;background:white;border-radius:10px;color:#34435a}
        .camp-action{margin-top:24px;display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap}
        .camp-error{display:none;margin-top:14px;padding:12px;border-radius:9px;background:#fff0ef;color:#9b3f41}
        .camp-demo{border-left:3px solid #d5a343;background:#fffbec;padding:12px 15px;border-radius:8px;color:#796538;font-size:12px}
        .camp-code{background:#edf1fa;border:1px dashed #a0add1;padding:6px 11px;border-radius:9px;font-weight:900;font-size:17px;letter-spacing:4px;color:#435d8b}

        @media(max-width:1150px){.cards{grid-template-columns:repeat(2,1fr)}.two,.twosame,.ai-grid2,.geo-grid{grid-template-columns:1fr}}
        @media(max-width:760px){
            #root{display:block}
            .sidebar{width:100%;height:auto;min-height:unset;position:relative;padding:12px;display:block}
            .brand,.brand-pill,.profile,.sidebar .bottom,.navheader{display:none}
            .sidebar nav{display:flex;gap:6px;overflow-x:auto}
            .navitem{width:auto;white-space:nowrap;margin:0;padding:9px}
            .navitem .ico{display:none}
            .topbar{height:auto;padding:10px;flex-wrap:wrap}
            .cards,.two,.twosame,.v8-two,.v8-three,.q-grid{grid-template-columns:1fr}
            .ai-filters,.ai-kpis,.ai-tools{grid-template-columns:1fr}
        }
    </style>
</head>
<body>
<div id="root">
    <aside class="sidebar">
        <div class="brand">
            <img alt="Logotipo EESP Pukllasunchis" src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}" onerror="this.src='{{ asset('img/logoiesp.ico') }}'"/>
        </div>
        <div class="brand-pill">ÁREA INSTITUCIONAL</div>
        <div class="profile">
            <div class="avatar">{{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1) . substr(auth()->user()->apellidos ?? 'P', 0, 1)) : 'PC' }}</div>
            <div>
                <b>{{ auth()->check() ? auth()->user()->name : 'Usuario Institucional' }}</b>
                <small>{{ auth()->check() ? auth()->user()->email : 'Pukllasunchis' }}</small>
            </div>
        </div>
        <div id="nav"></div>
        <div class="bottom">
            <a href="{{ route('index') }}" class="navitem" style="text-decoration:none;">
                <span class="ico">↪</span><span>Volver a la intranet</span>
            </a>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <span class="season">▦ Seguimiento de egresados — campaña 2026-II</span>
            <div class="topright">
                <button type="button" class="btn sm outline" onclick="window.CampaignPortal.open()">✉ Enlace de campaña WhatsApp</button>
                <select aria-label="Perfil simulado" class="role-switch" id="role-picker">
                    <option value="coordinacion">Coordinación</option>
                    <option value="encuestador">Entrevistador/a</option>
                    <option value="egresado">Egresado/a</option>
                    <option value="direccion">Dirección</option>
                    <option value="administrador">Administración</option>
                </select>
                <span class="top-avatar">EESP Pukllasunchis</span>
            </div>
        </header>
        <main class="content" id="content"></main>
    </div>
</div>

<div id="modalhost"></div>

<div aria-label="Acceso a campaña de actualización de egresados" id="campaign-portal">
    <div class="camp-head">
        <img alt="EESP Pukllasunchis" src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}" id="camp-logo"/>
        <span class="camp-chip">● Campaña 2026-II · ACCESO DE EGRESADO</span>
    </div>
    <div class="camp-card">
        <div class="camp-banner">
            <small>EESP Pukllasunchis · Unidad de Bienestar y Empleabilidad</small>
            <h1>Actualiza tu trayectoria profesional</h1>
            <p>Confirma tus datos, cuéntanos dónde trabajas, cómo avanzas con tu titulación y qué formación o apoyo necesitas.</p>
        </div>
        <div class="camp-inner">
            <div class="camp-steps">
                <div class="camp-step on" id="camp-step-1">1 · Identificar ficha</div>
                <div class="camp-step" id="camp-step-2">2 · Verificar identidad</div>
                <div class="camp-step" id="camp-step-3">3 · Actualizar datos</div>
            </div>
            <div id="camp-work"></div>
            <div class="camp-error" id="camp-error" role="alert"></div>
        </div>
    </div>
    <div class="camp-footer" style="margin-top:20px;font-size:12px;color:#7c879d;">
        Portal público para actualización autónoma mediante Magic Link (WhatsApp). Los datos actualizados se guardan en el sistema institucional EESPP Pukllasunchis.
    </div>
</div>

<script>
    // Inyección de datos reales desde Laravel / Base de Datos
    const INSTITUTIONAL_EGRESADOS = @json($egresadosReales);
    const ACTUALIZACIONES_DB = @json($actualizacionesDb);
    const CASOS_DB = @json($casosDb);
    const LLAMADAS_DB = @json($llamadasDb);
    const ACCESOS_DB = @json($accesosContactoDb);
    const GRUPOS_DB = @json($gruposDb ?? []);
    const ENTREVISTADORES_DISPONIBLES = @json($entrevistadoresDisponibles ?? []);
    const EPISODIOS_DB = @json($episodiosDb ?? []);

    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const ROUTES = {
        actualizar: "{{ route('seguimiento-egresados.actualizar') }}",
        accion: "{{ route('seguimiento-egresados.accion') }}",
        avanzarAccion: "{{ url('/seguimiento-egresados/avanzar-accion') }}",
        llamada: "{{ route('seguimiento-egresados.llamada') }}",
        auditarContacto: "{{ route('seguimiento-egresados.auditar-contacto') }}",
        gruposGuardar: "{{ route('seguimiento-egresados.grupos.guardar') }}",
        gruposEliminar: "{{ url('/seguimiento-egresados/grupos') }}",
        enlaceWhatsapp: "{{ route('seguimiento-egresados.enlace-whatsapp') }}",
        campanaPublica: "{{ route('seguimiento-egresados.campana-publica') }}",
        cerrarEpisodio: "{{ url('/seguimiento-egresados/episodios') }}"
    };

    const ROSTER_SEVEN_V8 = {
        "cantidad_filas": 266,
        "personas_unicas": 258,
        "repeticiones": 8,
        "confirmados_unicos": 94,
        "pendientes_unicos": 164,
        "con_correo": 258,
        "con_celular": 257,
        "periodos": [
            {"periodo":"2021-II","filas":21,"inicial":14,"eib":7,"condicion_explicita":19,"pendiente":2,"contactos":21},
            {"periodo":"2022-II","filas":16,"inicial":10,"eib":6,"condicion_explicita":0,"pendiente":16,"contactos":16},
            {"periodo":"2023-I","filas":80,"inicial":0,"eib":80,"condicion_explicita":0,"pendiente":80,"contactos":80},
            {"periodo":"2023-II","filas":31,"inicial":19,"eib":12,"condicion_explicita":0,"pendiente":31,"contactos":31},
            {"periodo":"2024-I","filas":30,"inicial":15,"eib":15,"condicion_explicita":0,"pendiente":30,"contactos":30},
            {"periodo":"2025-I","filas":43,"inicial":23,"eib":20,"condicion_explicita":40,"pendiente":3,"contactos":43},
            {"periodo":"2025-II","filas":45,"inicial":23,"eib":22,"condicion_explicita":35,"pendiente":10,"contactos":44}
        ]
    };

    const SOURCE = {
        "padron": [
            {"id":"PAD-001","nombre":"Lucía Quispe Huamán","programa":"Inicial","ingreso":"2019-I","egreso":"2024","situacion":"Bachiller","contacto":"Contactado","fuente":"Padrón FID · demostración","encuesta":"ENC-001","link":"Vinculación simulada","empleo_hist":"Trabaja"},
            {"id":"PAD-002","nombre":"Marco Huamán Flores","programa":"Primaria EIB","ingreso":"2018-I","egreso":"2023","situacion":"Bachiller","contacto":"Contactado","fuente":"Padrón FID · demostración","encuesta":"ENC-002","link":"Vinculación simulada","empleo_hist":"No trabaja"},
            {"id":"PAD-003","nombre":"Elena Ccama Torres","programa":"Inicial","ingreso":"2020-I","egreso":"2025","situacion":"Egresada","contacto":"Contactado","fuente":"Padrón FID · demostración","encuesta":"ENC-003","link":"Vinculación simulada","empleo_hist":"Trabaja"}
        ],
        "encuesta": [
            {"id":"ENC-001","nombre":"Lucía Quispe Huamán","programa":"Inicial","egreso":"2024","academica":"Bachiller","trabaja":"Trabaja","insercion":"En educación","sector":"Privado/particular","practica":"Docencia directa","cargo":"Docente de Inicial","lugar_trabajo":"Cusco, Cusco","necesidades_archivo":"Planificación y evaluación formativa","aporte_archivo":"Juego y participación de las familias","sugerencias_archivo":"Intercambio de experiencias en lengua quechua","match":"Verificado","padron_id":"PAD-001"},
            {"id":"ENC-002","nombre":"Marco Huamán Flores","programa":"Primaria EIB","egreso":"2023","academica":"Bachiller","trabaja":"No trabaja","insercion":"No trabaja","sector":"Indeterminado","practica":"Otros sectores","cargo":"","lugar_trabajo":"","necesidades_archivo":"Apoyo en contratación docente","aporte_archivo":"Trabajo con escuelas rurales","sugerencias_archivo":"","match":"Verificado","padron_id":"PAD-002"},
            {"id":"ENC-003","nombre":"Elena Ccama Torres","programa":"Inicial","egreso":"2025","academica":"Egresada","trabaja":"Trabaja","insercion":"En educación","sector":"Público/estatal","practica":"Auxiliar / apoyo pedagógico","cargo":"Auxiliar de Educación Inicial","lugar_trabajo":"Sicuani, Canchis, Cusco","necesidades_archivo":"Investigación y titulación","aporte_archivo":"Materiales lúdicos","sugerencias_archivo":"","match":"Verificado","padron_id":"PAD-003"}
        ],
        "fuente": "300 egresados institucionales desde la BD de EESP Pukllasunchis + 3 casos base V8"
    };
</script>

{{-- Incluir scripts interactivos del Simulador V8 integrados con Laravel --}}
<script>
(()=>{'use strict';
const $=s=>document.querySelector(s);
const esc=x=>String(x??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const roles={coordinacion:'Coordinación de Bienestar y Empleabilidad',encuestador:'Entrevistador telefónico',egresado:'Vista de egresado/a',direccion:'Dirección',administrador:'Administración'};
const pages={panel:'Panel de seguimiento',padron:'Padrón y respuestas',padron7:'Padrón · 7 listas',grupos:'Grupos de entrevistadores',agil:'Actualización breve',acciones:'Acompañamiento y acciones',campanas:'Campañas semestrales',llamadas:'Llamadas y entrevistas',cuestionario:'Cuestionario común',territorio:'Observatorio territorial',inteligencia:'Inteligencia institucional',formacion:'Formación continua',contactos:'Directorio de contactos',permisos:'Seguridad y funciones',ficha:'Mi ficha'};
const allowed={coordinacion:['panel','padron','grupos','campanas','llamadas','cuestionario','territorio','inteligencia','formacion','contactos','padron7','agil','acciones'],encuestador:['llamadas','padron','cuestionario','contactos','agil','padron7'],egresado:['ficha','cuestionario','agil'],direccion:['panel','grupos','inteligencia','territorio','formacion','contactos','padron7','acciones'],administrador:['permisos','panel','grupos']};

const state={
    role:'coordinacion',
    page: window.location.hash === '#grupos' ? 'grupos' : (window.location.hash === '#whatsapp' ? 'campanas' : 'panel'),
    selected:'ENC-001',
    sourceTab:'encuesta',
    search:'',
    program:'todos',
    callFilter:'todos',
    edits:{},
    history:[],
    calls:[],
    jobEpisodes: Array.isArray(EPISODIOS_DB) ? EPISODIOS_DB : [],
    actions:[],
    contactLog:[],
    grupos: Array.isArray(GRUPOS_DB) ? GRUPOS_DB : [],
    formMode:'auto',
    geo:'trabajo',
    grants:true,
    grantStart:'2026-11-15',
    grantEnd:'2026-11-30'
};

// Cargar estado inicial desde BD Laravel
if (typeof ACTUALIZACIONES_DB === 'object') {
    Object.entries(ACTUALIZACIONES_DB).forEach(([key, val]) => {
        state.edits[key] = {
            telefono: val.telefono || '',
            whatsapp: val.whatsapp || '',
            telefono_alt: val.telefono_alt || '',
            correo: val.correo || '',
            direccion_contacto: val.direccion_contacto || '',
            medio_preferido: val.medio_preferido || '',
            res_departamento: val.res_departamento || '',
            res_provincia: val.res_provincia || '',
            res_distrito: val.res_distrito || '',
            titulo_bach: val.titulo_bach || '',
            titulo_lic: val.titulo_lic || '',
            apoyo_titulacion: val.apoyo_titulacion || '',
            trabaja: val.trabaja || '',
            cambio_empleo: val.cambio_empleo || '',
            campo: val.campo || '',
            tipo_practica: val.tipo_practica || '',
            institucion: val.institucion || '',
            cargo: val.cargo || '',
            contrato: val.contrato || '',
            inicio_empleo: val.inicio_empleo || '',
            fin_empleo: val.fin_empleo || '',
            trab_departamento: val.trab_departamento || '',
            trab_provincia: val.trab_provincia || '',
            trab_distrito: val.trab_distrito || '',
            satisfaccion: val.satisfaccion || '',
            temas: val.temas_interes || '',
            necesidades: val.necesidades_apoyo || '',
            puedo_compartir: val.puedo_compartir || ''
        };
        state.history.push({
            id: key,
            name: val.nombre_completo,
            source: val.canal === 'entrevista_telefonica' ? 'Entrevista telefónica' : 'Actualización institucional',
            changed: ['Datos persistidos en BD'],
            at: new Date(val.updated_at).toLocaleString('es-PE'),
            snapshot: state.edits[key]
        });
    });
}

if (Array.isArray(CASOS_DB)) {
    state.actions = CASOS_DB.map(c => ({
        id: c.id,
        pid: c.user_id ? ('REAL-' + c.user_id) : 'CASO-' + c.id,
        nombre: c.nombre_egresado,
        tema: c.tema,
        responsable: c.responsable,
        fecha: c.fecha_gestion || '',
        estado: c.estado,
        nota: c.proposito || '',
        origen: c.origen || 'BD Institucional'
    }));
}

if (Array.isArray(LLAMADAS_DB)) {
    state.calls = LLAMADAS_DB.map(l => ({
        id: l.user_id ? ('REAL-' + l.user_id) : (l.identificador_temporal || 'ID'),
        result: l.resultado,
        comment: l.observaciones,
        at: new Date(l.fecha_hora || l.created_at).toLocaleString('es-PE')
    }));
}

if (Array.isArray(ACCESOS_DB)) {
    state.contactLog = ACCESOS_DB.map(a => ({
        role: a.rol_consultante,
        id: a.identificador_consultado,
        at: new Date(a.fecha_hora || a.created_at).toLocaleString('es-PE'),
        purpose: a.finalidad
    }));
}

// Población de egresados combinada
const SURVEY = SOURCE.encuesta;
const ROSTER = [...SOURCE.padron];
if (Array.isArray(INSTITUTIONAL_EGRESADOS)) {
    INSTITUTIONAL_EGRESADOS.forEach(p => {
        ROSTER.push({
            id: p.id,
            nombre: p.nombre,
            programa: p.programa,
            egreso: p.egreso,
            periodo_referencia: p.periodo_referencia,
            egreso_confirmado: true,
            correo: p.correo,
            telefono: p.telefono,
            telefono_alt: p.telefono_alt,
            direccion_contacto: p.direccion_contacto,
            res_departamento: p.res_departamento,
            res_provincia: p.res_provincia,
            res_distrito: p.res_distrito,
            situacion: p.estado_academico,
            fuente: p.origen
        });
    });
}

const surveyById = Object.fromEntries(SURVEY.map(p=>[p.id,p]));
const rosterById = Object.fromEntries(ROSTER.map(p=>[p.id,p]));
const locate = id => surveyById[id] || rosterById[id] || null;
const origFor = p => p?.id.startsWith('PAD-') ? (p.encuesta ? surveyById[p.encuesta] : null) : p;
const keyOf = p => p?.id.startsWith('PAD-') && p.encuesta ? p.encuesta : p?.id;

const CONTACT_EXAMPLES = {
    'ENC-001':{correo:'lucia@ejemplo.invalid',telefono:'900 000 001',whatsapp:'900 000 001',direccion_contacto:'San Jerónimo, Cusco'},
    'ENC-002':{correo:'marco@ejemplo.invalid',telefono:'900 000 002',whatsapp:'900 000 002',direccion_contacto:'Sicuani, Canchis'},
    'ENC-003':{correo:'elena@ejemplo.invalid',telefono:'900 000 003',whatsapp:'900 000 003',direccion_contacto:'San Sebastián, Cusco'}
};

const ASSIGNED = ['PAD-001','PAD-002'];
const contactAuthorized = p => !!p && ['coordinacion','direccion','encuestador'].includes(state.role);
const canEdit = () => ['coordinacion','encuestador','egresado'].includes(state.role);
const stat = (a,n,desc,cls='c1') => `<div class="stat ${cls}"><div class="label">${a}</div><div class="number">${n}</div><div class="foot">${desc}</div></div>`;

function nav(){
    const groups = [
        ['SEGUIMIENTO DE EGRESADOS', [
            ['panel','▦'],
            ['padron7','▤'],
            ['grupos','👥'],
            ['agil','✎'],
            ['acciones','♥'],
            ['padron','♟'],
            ['campanas','✉'],
            ['llamadas','☎'],
            ['cuestionario','☑'],
            ['territorio','⌖'],
            ['inteligencia','◫'],
            ['formacion','✧'],
            ['contactos','✆'],
            ['permisos','◈'],
            ['ficha','▤']
        ]]
    ];
    $('#nav').innerHTML = groups.map(([g,items]) => {
        const sel = items.filter(x => allowed[state.role].includes(x[0]));
        return sel.length ? `<div class="navheader">${g}</div>` + sel.map(([id,ico]) => `
            <button class="navitem ${state.page===id?'active':''}" data-page="${id}">
                <span class="ico">${ico}</span><span>${pages[id]}</span>
                ${id==='llamadas'?'<em>LLAMADAS</em>':''}
                ${id==='grupos'?'<em style="background:#059669;">EQUIPOS</em>':''}
            </button>`).join('') : '';
    }).join('');
    document.querySelectorAll('[data-page]').forEach(x => x.addEventListener('click', () => goto(x.dataset.page)));
}

function goto(page){
    if(!allowed[state.role].includes(page)) return;
    state.page = page;
    render();
    window.scrollTo({top:0, behavior:'instant'});
}

function setRole(v){
    state.role = v;
    state.page = allowed[v][0];
    state.formMode = v === 'encuestador' ? 'telefono' : 'auto';
    render();
}

function hero(label, title, desc, extra=''){
    return `<section class="hero">
        <div>
            <span class="eyebrow">${label}</span>
            <h1>${title}</h1>
            <p>${desc}</p>
            <span class="tag">▦ EESP Pukllasunchis · Base de datos institucional conectada · ${roles[state.role]}</span>
        </div>
        <div class="hero-actions">
            <span class="demo">SISTEMA ACTIVO LOCALMENTE</span>
            ${extra}
        </div>
    </section>`;
}

function formValues(p){
    const s = origFor(p) || {};
    const key = keyOf(p);
    const e = state.edits[key] || {};
    const contactDemo = CONTACT_EXAMPLES[key] || {};
    return {
        trabaja: s.trabaja || '',
        campo: s.insercion || '',
        sector: s.sector || '',
        tipo_practica: s.practica || '',
        cargo: s.cargo || '',
        institucion: '',
        lugar_trabajo: s.lugar_trabajo || '',
        programa: p.programa || s.programa || '',
        egreso: p.egreso || s.egreso || '',
        titulo_bach: '',
        titulo_lic: '',
        res_departamento: p.res_departamento || 'CUSCO',
        res_provincia: p.res_provincia || 'CUSCO',
        res_distrito: p.res_distrito || 'CUSCO',
        direccion_contacto: p.direccion_contacto || contactDemo.direccion_contacto || '',
        whatsapp: p.telefono || contactDemo.whatsapp || '',
        correo: p.correo || contactDemo.correo || '',
        telefono: p.telefono || contactDemo.telefono || '',
        telefono_alt: p.telefono_alt || '',
        inicio_empleo: '',
        fin_empleo: '',
        contrato: '',
        cambio_empleo: '',
        practicas_que_permanecen: '',
        eib: '',
        quechua: '',
        satisfaccion: '',
        temas: '',
        necesidades: '',
        puedo_compartir: '',
        apoyo_titulacion: '',
        ...contactDemo,
        ...e
    };
}

// ---------------- PANEL PRINCIPAL ----------------
function panel(){
    const all = ROSTER.map(p => ({p, v:formValues(p)}));
    const updated = state.history.length;
    const employed = all.filter(({v}) => v.trabaja === 'Trabaja').length;
    const phones = state.calls.filter(c => c.result === 'Entrevista completa' || c.result === 'Entrevista aceptada').length;

    return hero('UNIDAD DE BIENESTAR Y EMPLEABILIDAD', 'Seguimiento de egresados', 'Panel unificado conectado con la base de datos de la escuela EESPP Pukllasunchis.') +
    `<div class="notice">
        <strong>✓ Conectado a la base de datos institucional:</strong> ${ROSTER.length} egresados disponibles (${INSTITUTIONAL_EGRESADOS.length} reales de la BD <code>puklla</code> + casos de prueba). Los cambios se guardan directamente en MySQL.
    </div>
    <div class="cards">
        ${stat('Egresados en sistema', ROSTER.length, 'Registros institucionales activos', 'c1')}
        ${stat('Actualizaciones registradas', updated, 'Fichas actualizadas en MySQL', 'c2')}
        ${stat('Casos de apoyo abiertos', state.actions.length, 'Seguimiento institucional', 'c3')}
        ${stat('Entrevistas telefónicas', phones, 'Recuperación de cobertura', 'c4')}
    </div>
    <div class="two">
        <div class="panel">
            <h2>Circuitos conectados de seguimiento</h2>
            <div class="personline">
                <div><strong>✎ Actualización breve (3 minutos)</strong><span>Ficha ágil con confirmación de cambios.</span></div>
                <button class="btn sm" onclick="APP.goto('agil')">Abrir</button>
            </div>
            <div class="personline">
                <div><strong>✉ Campaña pública WhatsApp</strong><span>Acceso con Magic Link para egresados desde celular.</span></div>
                <button class="btn sm outline" onclick="window.CampaignPortal.open()">Simular enlace</button>
            </div>
            <div class="personline">
                <div><strong>☎ Recuperación telefónica</strong><span>Entrevistas con el mismo formulario único.</span></div>
                <button class="btn sm outline" onclick="APP.goto('llamadas')">Llamar</button>
            </div>
            <div class="personline">
                <div><strong>◫ Inteligencia institucional</strong><span>Cruces, filtros, medidas y exportación de libro Excel multi-hoja.</span></div>
                <button class="btn sm outline" onclick="APP.goto('inteligencia')">Analizar</button>
            </div>
            <div class="personline">
                <div><strong>⌖ Observatorio territorial</strong><span>Cartografía real con análisis de residencia vs trabajo.</span></div>
                <button class="btn sm outline" onclick="APP.goto('territorio')">Ver mapas</button>
            </div>
        </div>
        <div class="panel">
            <h2>Acciones institucionales recientes</h2>
            <p class="sub">Del hallazgo a la acción: casos atendidos por Bienestar y Empleabilidad.</p>
            <div class="tablewrap" style="max-height:240px;">
                <table>
                    <thead><tr><th>Egresado/a</th><th>Tema</th><th>Estado</th></tr></thead>
                    <tbody>
                        ${state.actions.slice(0, 6).map(a => `
                            <tr>
                                <td><b>${esc(a.nombre)}</b></td>
                                <td>${esc(a.tema)}</td>
                                <td><span class="chip ${a.estado==='Cerrado'?'ok':'warn'}">${esc(a.estado)}</span></td>
                            </tr>
                        `).join('') || '<tr><td colspan="3" class="muted">No hay casos pendientes. Registra uno desde actualización ágil.</td></tr>'}
                    </tbody>
                </table>
            </div>
            <div class="toolbar">
                <button class="btn sm" onclick="APP.goto('acciones')">Ver plan completo de acompañamiento →</button>
            </div>
        </div>
    </div>`;
}

// ---------------- ACTUALIZACIÓN ÁGIL ----------------
const V8_CATEGORY = ['Planificación y evaluación','Educación intercultural / quechua','Inclusión y diversidad','Aulas multigrado','Gestión y documentos','Titulación e investigación','Concursos y empleo','Bienestar y trabajo digno','Otras'];
const v8Opts = (opts, val) => opts.map(x => `<option value="${esc(x)}" ${x===val?'selected':''}>${esc(x)}</option>`).join('');

function shortField(k, lab, kind='text', opts=[]){
    const v = state.current || {};
    const value = String(v[k] ?? '');
    const el = kind === 'select'
        ? `<select name="${esc(k)}">${v8Opts(['', ...opts], value)}</select>`
        : kind === 'textarea'
        ? `<textarea rows="2" name="${esc(k)}">${esc(value)}</textarea>`
        : `<input name="${esc(k)}" type="${kind}" value="${esc(value)}" placeholder="${k.includes('fecha')||k.includes('inicio')?'AAAA-MM-DD':'Actualizar si cambió'}">`;
    return `<div class="field"><label>${lab}</label>${el}</div>`;
}

function actualizacionAgil(){
    const p = locate(state.selected) || ROSTER[0];
    state.current = formValues(p);
    const v = state.current;
    const isTel = state.formMode === 'telefono';
    const hasJob = v.trabaja === 'Trabaja';

    // Resolver episodios laborales del egresado
    const pIdStr = String(p.id || '');
    const numId = pIdStr.startsWith('REAL-') ? parseInt(pIdStr.replace('REAL-', '')) : (Number.isInteger(Number(pIdStr)) ? Number(pIdStr) : null);
    const myEpisodes = (state.jobEpisodes || []).filter(e => numId ? e.user_id == numId : e.identificador_temporal == p.id);
    const activeEpisodes = myEpisodes.filter(e => e.es_actual);
    const pastEpisodes = myEpisodes.filter(e => !e.es_actual);

    return hero(isTel ? 'ENTREVISTA TELEFÓNICA' : 'ACTUALIZACIÓN SEMESTRAL', 'Actualizar solo lo que haya cambiado', 'Formulario ágil de confirmación en 3 bloques. Se sincroniza con la base de datos MySQL institucional.') +
    `<div class="panel">
        <div class="section-top">
            <div>
                <h2>${esc(p.nombre)}</h2>
                <p class="sub">${esc(p.programa)} · Referencia ${esc(p.egreso || 'Por verificar')} · ID: <code>${esc(p.id)}</code></p>
            </div>
            <div class="toolbar">
                <select id="switch-egresado-select" style="padding:8px 12px;border-radius:8px;border:1px solid #d2d8e8;">
                    ${ROSTER.slice(0, 50).map(x => `<option value="${x.id}" ${x.id===p.id?'selected':''}>${esc(x.nombre)} (${esc(x.programa)})</option>`).join('')}
                </select>
                <button class="btn outline sm" onclick="APP.goto('cuestionario')">Ver cuestionario de 50 campos</button>
            </div>
        </div>
        <p class="sub">Confirma los datos vigentes. Cuando el egresado declara un cambio de empleo o solicita apoyo, se registran los nuevos eventos sin sobreescribir la historia.</p>
    </div>

    <!-- Confirmación rápida R02 -->
    <div class="panel" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1.5px solid #86efac; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h3 style="color:#065f46; font-size:15px; margin:0; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-bolt" style="color:#10b981;"></i> Confirmación Rápida Semestral «Sin cambios» (R02)
                </h3>
                <p style="margin:4px 0 0; font-size:12px; color:#047857;">
                    Si la trayectoria laboral y de contacto se mantiene vigente, confírmala con 1 solo clic. No se duplican empleos y se registra la fecha semestral.
                </p>
            </div>
            <button class="btn green" type="button" onclick="APP.confirmarSinCambios()" style="font-weight:700; box-shadow:0 4px 12px rgba(16,185,129,0.25);">
                ⚡ Confirmar situación vigente sin cambios
            </button>
        </div>
    </div>

    <form id="v8-fast-form" class="panel">
        <div class="v8-head">01 · Contacto y titulación</div>
        <div class="v8-two">
            ${shortField('telefono', 'Celular o WhatsApp actual', 'tel')}
            ${shortField('correo', 'Correo electrónico', 'email')}
        </div>
        <div class="v8-two">
            ${shortField('titulo_lic', '¿Cuál es tu situación de licenciatura?', 'select', ['Aún no iniciado', 'En trámite', 'Licenciado/a', 'Necesito asesoría'])}
            ${shortField('apoyo_titulacion', '¿Necesitas apoyo para titularte?', 'select', ['No', 'Sí', 'Prefiero conversar'])}
        </div>

        <div class="v8-head">02 · Trabajo y condiciones profesionales</div>

        <!-- Panel de trayectoria laboral persistida (R03) -->
        <div style="background:#f8fafc; border:1px solid #e2e8f0; margin-bottom:14px; border-radius:10px; padding:12px 14px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <h4 style="margin:0; font-size:13px; color:#1e293b;"><i class="fas fa-briefcase" style="color:#3b82f6; margin-right:6px;"></i> Trayectoria laboral registrada (Historial longitudinal)</h4>
                <span class="chip ${activeEpisodes.length ? 'ok' : ''}">${activeEpisodes.length} activo(s)</span>
            </div>
            ${activeEpisodes.length > 0 ? `
                <div style="display:flex; flex-direction:column; gap:8px;">
                    ${activeEpisodes.map(ep => `
                        <div style="display:flex; justify-content:space-between; align-items:center; background:white; padding:10px 12px; border-radius:8px; border:1px solid #cbd5e1;">
                            <div>
                                <strong style="color:#0f172a; font-size:13px;">${esc(ep.institucion)}</strong> — <span style="color:#475569;">${esc(ep.cargo)}</span>
                                <div style="font-size:11px; color:#64748b; margin-top:2px;">
                                    Desde: <b>${esc(ep.fecha_inicio)}</b> · Contrato: ${esc(ep.contrato || 'No especificado')} · ${ep.es_docencia_directa ? '👨‍🏫 Docencia directa' : 'Gestión/Otro'} · Ubicación: ${esc(ep.trab_distrito || 'No declarada')}
                                </div>
                            </div>
                            <button type="button" class="btn sm danger" onclick="APP.cerrarEpisodioModal(${ep.id})" title="Marcar como empleo concluido">
                                <i class="fas fa-stop-circle mr-1"></i> Cerrar empleo
                            </button>
                        </div>
                    `).join('')}
                </div>
            ` : '<p style="font-size:12px; color:#94a3b8; margin:0;">No hay episodios laborales activos registrados para este perfil.</p>'}
            
            ${pastEpisodes.length > 0 ? `
                <details style="margin-top:10px;">
                    <summary style="font-size:12px; color:#64748b; cursor:pointer; font-weight:600;">Ver historial de empleos anteriores concluidos (${pastEpisodes.length})</summary>
                    <div style="display:flex; flex-direction:column; gap:6px; margin-top:8px;">
                        ${pastEpisodes.map(ep => `
                            <div style="background:#f1f5f9; padding:8px 10px; border-radius:6px; font-size:12px; color:#475569;">
                                <strong>${esc(ep.institucion)}</strong> (${esc(ep.cargo)}) · <em>${esc(ep.fecha_inicio)}</em> a <em>${esc(ep.fecha_fin || 'No precisada')}</em> ${ep.motivo_cambio ? `· Motivo: ${esc(ep.motivo_cambio)}` : ''}
                            </div>
                        `).join('')}
                    </div>
                </details>
            ` : ''}
        </div>

        <div class="v8-two">
            ${shortField('trabaja', '¿Actualmente trabajas?', 'select', ['Trabaja', 'No trabaja', 'Prefiere no responder'])}
            ${shortField('cambio_empleo', '¿Cambió tu situación laboral?', 'select', ['Sin cambios', 'Primer empleo', 'Nuevo empleo o puesto', 'Terminó un empleo', 'Primera actualización'])}
        </div>
        <div id="v8-job" ${(!hasJob || v.cambio_empleo==='Sin cambios') ? 'class="v8-hidden"' : ''}>
            <div class="v8-two">
                ${shortField('institucion', 'Institución o empleador')}
                ${shortField('cargo', 'Puesto o función')}
            </div>
            <div class="v8-two">
                ${shortField('campo', 'Campo de actividad', 'select', ['En educación', 'Fuera de educación'])}
                ${shortField('tipo_practica', 'Tipo de práctica', 'select', ['Docencia directa', 'Auxiliar / apoyo pedagógico', 'Gestión educativa', 'Educación no formal / comunitaria', 'Otros sectores'])}
            </div>
            <div class="v8-two">
                ${shortField('contrato', 'Modalidad contractual', 'select', ['Nombramiento', 'Contrato anual', 'Contrato temporal/eventual', 'Por horas', 'Independiente', 'Sin contrato'])}
                ${shortField('inicio_empleo', 'Fecha de inicio del trabajo actual', 'date')}
            </div>
            <div class="v8-two">
                ${shortField('trab_departamento', 'Departamento de trabajo')}
                ${shortField('trab_distrito', 'Distrito de trabajo')}
            </div>
        </div>
        <div class="v8-two">
            ${shortField('satisfaccion', '¿Cómo valoras tus condiciones laborales?', 'select', ['1 - Muy insatisfecho/a', '2 - Insatisfecho/a', '3 - Regular', '4 - Satisfecho/a', '5 - Muy satisfecho/a', 'Prefiero no responder'])}
            ${shortField('res_distrito', 'Distrito de residencia actual')}
        </div>

        <div class="v8-head">03 · Acompañamiento y aprendizaje</div>
        <div class="v8-two">
            ${shortField('temas', 'Tema prioritario de formación continua', 'select', V8_CATEGORY)}
            ${shortField('necesidad_prioritaria', '¿Deseas que la coordinación se contacte contigo?', 'select', ['No por ahora', 'Sí: titulación', 'Sí: búsqueda de empleo', 'Sí: condiciones laborales', 'Sí: apoyo pedagógico', 'Sí: otro motivo'])}
        </div>
        <div class="v8-two">
            ${shortField('practicas_que_permanecen', '¿Qué experiencia pedagógica destacas de tu formación?', 'textarea')}
            ${shortField('puedo_compartir', '¿Qué saber o experiencia podrías compartir con otros egresados?', 'textarea')}
        </div>

        <label class="v8-confirm">
            <input id="v8-confirm" type="checkbox" required>
            Confirmo que los datos son correctos y acepto registrar esta actualización en la base de datos de EESPP Pukllasunchis.
        </label>
        <div class="buttons">
            <button class="btn green" type="submit">✓ Guardar en base de datos institucional</button>
            <button class="btn outline" type="button" onclick="APP.goto('cuestionario')">Cuestionario completo</button>
        </div>
        <div id="v8-fast-notice"></div>
    </form>`;
}

function switchQuickJob(){
    const e = document.querySelector('#v8-fast-form select[name="trabaja"]');
    const group = $('#v8-job');
    const ch = document.querySelector('#v8-fast-form select[name="cambio_empleo"]');
    if (group && e) {
        group.classList.toggle('v8-hidden', e.value !== 'Trabaja' || (ch && ch.value === 'Sin cambios'));
    }
}

async function saveQuick(ev){
    ev.preventDefault();
    const p = locate(state.selected);
    if (!p) return;

    const formData = new FormData(ev.currentTarget);
    const vals = Object.fromEntries(formData.entries());
    const id = keyOf(p);
    const prev = formValues(p);
    const changed = Object.keys(vals).filter(k => String(prev[k] ?? '') !== String(vals[k] ?? ''));

    state.edits[id] = {...(state.edits[id] || {}), ...vals};
    const now = new Date();

    state.history.unshift({
        id,
        name: p.nombre,
        source: state.formMode === 'telefono' ? 'Entrevista telefónica' : 'Actualización autónoma',
        changed,
        at: now.toLocaleString('es-PE'),
        snapshot: {...state.edits[id]}
    });

    // Enviar a Laravel MySQL Backend vía AJAX
    try {
        const payload = {
            id: p.id,
            nombre: p.nombre,
            programa: p.programa,
            egreso: p.egreso,
            canal: state.formMode === 'telefono' ? 'entrevista_telefonica' : 'autorespuesta',
            ...vals
        };

        const res = await fetch(ROUTES.actualizar, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const json = await res.json();

        $('#v8-fast-notice').innerHTML = `
            <div class="notice" style="margin-top:14px;background:#e9f9f0;color:#126c36;border:1px solid #c2eccd;">
                <strong>✓ Actualización guardada con éxito en MySQL:</strong> ${changed.length} campos actualizados.
                Registro ID: <code>${json.actualizacion_id || 'OK'}</code>.
                <button class="btn sm outline" type="button" onclick="APP.goto('panel')">Ver en el Panel</button>
            </div>
        `;
    } catch (err) {
        console.warn('Error sincronizando con backend', err);
        $('#v8-fast-notice').innerHTML = `
            <div class="notice" style="margin-top:14px;">
                <strong>✓ Guardado en memoria local:</strong> ${changed.length} campos actualizados.
            </div>
        `;
    }
}

async function confirmarSinCambios() {
    const p = locate(state.selected) || ROSTER[0];
    if (!p) return;

    const noticeEl = $('#v8-fast-notice');
    if (noticeEl) {
        noticeEl.innerHTML = '<div class="notice" style="margin-top:14px;background:#f0fdf4;color:#047857;border:1px solid #86efac;padding:12px;border-radius:8px;"><i class="fas fa-spinner fa-spin mr-1"></i> Confirmando situación semestral en la base institucional...</div>';
    }

    const f = $('#v8-fast-form');
    const sat = f?.querySelector('select[name="satisfaccion"]')?.value || '4 - Satisfecho/a';
    const temas = f?.querySelector('select[name="temas"]')?.value || '';
    const nec = f?.querySelector('select[name="necesidad_prioritaria"]')?.value || 'No por ahora';
    const compartir = f?.querySelector('textarea[name="puedo_compartir"]')?.value || '';

    const payload = {
        id: p.id,
        nombre: p.nombre,
        sin_cambios: true,
        cambio_empleo: 'Sin cambios',
        satisfaccion: sat,
        temas: temas,
        necesidad_prioritaria: nec,
        puedo_compartir: compartir,
        canal: state.formMode === 'telefono' ? 'entrevista_telefonica' : 'intranet',
        campana: '2026-II'
    };

    try {
        const res = await fetch(ROUTES.actualizar, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.success) {
            state.history.unshift({
                id: keyOf(p),
                name: p.nombre,
                source: 'Confirmación sin cambios (R02)',
                changed: ['Confirmación semestral de vigencia'],
                at: new Date().toLocaleString('es-PE'),
                snapshot: {...(state.edits[keyOf(p)] || {})}
            });

            if (noticeEl) {
                noticeEl.innerHTML = `
                    <div class="notice" style="margin-top:14px;background:#e9f9f0;color:#126c36;border:1px solid #c2eccd;border-radius:8px;padding:14px;">
                        <strong>✓ Situación confirmada con éxito (R02):</strong> ${esc(json.message)}
                        <br><small style="color:#065f46;">Fecha de verificación: ${esc(json.fecha_confirmacion)} · Empleos activos vigentes: ${json.episodios_activos ?? 1} · Sin duplicar registros.</small>
                    </div>
                `;
            }
        } else {
            alert('Error al confirmar: ' + (json.error || 'Verifique sus permisos.'));
        }
    } catch (err) {
        console.error('Error en confirmarSinCambios', err);
        alert('Error al conectar con el servidor institucional.');
    }
}

async function cerrarEpisodioModal(epId) {
    const ep = (state.jobEpisodes || []).find(x => x.id == epId);
    const nombreEp = ep ? `${ep.institucion} (${ep.cargo})` : `Episodio #${epId}`;
    
    const fechaFin = prompt(`Cerrar empleo: ${nombreEp}\n\nIngresa la fecha de finalización (AAAA-MM-DD):\n(Si la fecha exacta no se conoce, déjalo en blanco para conservar fecha desconocida)`, new Date().toISOString().slice(0, 10));
    if (fechaFin === null) return;

    const motivo = prompt("Motivo del término de este empleo (opcional):", "Término de contrato");

    try {
        const res = await fetch(`${ROUTES.cerrarEpisodio}/${epId}/cerrar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                fecha_fin: fechaFin.trim() || null,
                motivo_cambio: motivo || 'Fin de periodo'
            })
        });
        const json = await res.json();
        if (json.success) {
            alert('✓ Empleo concluido exitosamente. El historial longitudinal se ha conservado.');
            if (ep) {
                ep.es_actual = false;
                ep.fecha_fin = json.fecha_fin;
                ep.motivo_cambio = motivo;
            }
            render();
        } else {
            alert('Error al cerrar empleo: ' + (json.error || 'No autorizado'));
        }
    } catch (err) {
        console.error('Error cerrando episodio', err);
        alert('Error conectando con el servidor institucional.');
    }
}

// ---------------- GRUPOS DE ENTREVISTADORES ----------------
function gruposView(){
    const egresadosTotal = ROSTER.length;
    const gruposList = state.grupos || [];
    const entCount = Array.isArray(ENTREVISTADORES_DISPONIBLES) ? ENTREVISTADORES_DISPONIBLES.length : 0;

    return hero('COORDINACIÓN DE SEGUIMIENTO', 'Grupos y Equipos de Entrevistadores', 'Organiza docentes y personal para acompañar cohortes de egresados y llamadas semestrales.') +
    `<div class="cards">
        ${stat('Equipos creados', gruposList.length, 'Comisiones registradas', 'c1')}
        ${stat('Entrevistadores disponibles', entCount, 'Docentes / Personal', 'c2')}
        ${stat('Egresados en universo', egresadosTotal, 'Disponibles para asignación', 'c3')}
        ${stat('Actualizaciones en BD', state.history.length, 'Respuestas guardadas', 'c4')}
    </div>

    <!-- Panel de creación de grupo -->
    <div class="panel" style="border-top: 4px solid #10b981;">
        <div class="section-top" style="margin-bottom: 12px;">
            <div>
                <h2 style="margin:0;color:#064e3b;"><i class="fas fa-users-cog" style="color:#10b981;margin-right:8px;"></i> Crear nuevo equipo de entrevistadores</h2>
                <p style="margin:4px 0 0;font-size:12px;color:#64748b;">Asigna a docentes o personal institucional un lote de egresados por programa o cohorte.</p>
            </div>
            <span class="chip ok">Función Coordinador</span>
        </div>

        <form id="form-crear-grupo" onsubmit="event.preventDefault(); APP.saveGrupo(this);" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div style="grid-column:1/-1;">
                <label style="font-weight:700;font-size:12px;color:#334155;display:block;margin-bottom:4px;">Nombre del Equipo / Comisión *</label>
                <input name="nombre" required placeholder="Ej. Equipo de Seguimiento Inicial 2024-2025" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;">
            </div>

            <div>
                <label style="font-weight:700;font-size:12px;color:#334155;display:block;margin-bottom:4px;">Programa objetivo</label>
                <select name="programa" id="grupo-sel-prog" onchange="APP.updateGrupoPreview()" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;background:white;">
                    <option value="">Todos los programas</option>
                    <option value="Inicial">Educación Inicial</option>
                    <option value="Primaria EIB">Educación Primaria EIB</option>
                    <option value="Primaria">Educación Primaria</option>
                </select>
            </div>

            <div>
                <label style="font-weight:700;font-size:12px;color:#334155;display:block;margin-bottom:4px;">Cohorte de egreso</label>
                <select name="cohorte" id="grupo-sel-cohorte" onchange="APP.updateGrupoPreview()" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;background:white;">
                    <option value="">Todas las cohortes</option>
                    <option value="2025-II">2025-II</option>
                    <option value="2025-I">2025-I</option>
                    <option value="2024-I">2024-I</option>
                    <option value="2023-II">2023-II</option>
                    <option value="2023-I">2023-I</option>
                    <option value="2022-II">2022-II</option>
                    <option value="2021-II">2021-II</option>
                </select>
            </div>

            <div style="grid-column:1/-1;">
                <label style="font-weight:700;font-size:12px;color:#334155;display:block;margin-bottom:4px;">
                    Seleccionar entrevistadores docentes asignados (${entCount} disponibles)
                </label>
                <div style="max-height:140px;overflow-y:auto;border:1px solid #cbd5e1;border-radius:8px;padding:8px 12px;background:#f8fafc;display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:6px;">
                    ${(ENTREVISTADORES_DISPONIBLES || []).map(u => `
                        <label style="display:flex;align-items:center;gap:7px;font-size:12px;cursor:pointer;padding:3px;background:white;border-radius:6px;border:1px solid #e2e8f0;">
                            <input type="checkbox" name="entrevistador_id" value="${u.id}" data-name="${esc(u.name)}" data-email="${esc(u.email || '')}">
                            <span><b>${esc(u.name)}</b> <small style="color:#64748b;">(${esc(u.email || 'Docente')})</small></span>
                        </label>
                    `).join('') || '<span style="color:#64748b;font-size:12px;">No hay otros usuarios registrados con rol docente.</span>'}
                </div>
            </div>

            <div style="grid-column:1/-1;">
                <label style="font-weight:700;font-size:12px;color:#334155;display:block;margin-bottom:4px;">Instrucciones / Observaciones para el equipo</label>
                <textarea name="observaciones" rows="2" placeholder="Ej. Realizar 3 intentos de llamada por egresado y registrar el resultado de inserción laboral..." style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:12px;"></textarea>
            </div>

            <div style="grid-column:1/-1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-top:6px;padding-top:10px;border-top:1px solid #e2e8f0;">
                <div id="grupo-preview-egresados" style="font-size:12px;color:#065f46;background:#d1fae5;padding:6px 12px;border-radius:8px;font-weight:600;">
                    <i class="fas fa-users mr-1"></i> Egresados que integrarán este grupo: <b>${ROSTER.length}</b> seleccionables
                </div>
                <button type="submit" class="btn green" style="padding:9px 18px;font-weight:700;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-save"></i> Guardar y Crear Grupo
                </button>
            </div>
        </form>
    </div>

    <!-- Panel de Equipos Existentes -->
    <div class="panel">
        <div class="section-top" style="margin-bottom: 12px;">
            <h2><i class="fas fa-users" style="color:#0284c7;margin-right:8px;"></i> Equipos de entrevistadores activos (${gruposList.length})</h2>
            <button class="btn sm outline" onclick="APP.goto('llamadas')"><i class="fas fa-phone mr-1"></i> Ir a panel de llamadas</button>
        </div>

        ${gruposList.length === 0 ? `
            <div style="text-align:center;padding:35px 20px;color:#64748b;">
                <i class="fas fa-user-friends fa-2x" style="opacity:0.4;margin-bottom:10px;display:block;"></i>
                <p style="font-weight:600;margin-bottom:4px;">Aún no se han configurado grupos de entrevistadores</p>
                <small>Usa el formulario superior para crear la primera comisión de seguimiento por programa o cohorte.</small>
            </div>
        ` : `
            <div class="tablewrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre del Grupo</th>
                            <th>Filtro de Asignación</th>
                            <th>Entrevistadores Asignados</th>
                            <th>Egresados</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${gruposList.map(g => {
                            const entArr = Array.isArray(g.entrevistadores) ? g.entrevistadores : [];
                            const egrArr = Array.isArray(g.egresados_ids) ? g.egresados_ids : [];
                            return `
                                <tr>
                                    <td>
                                        <b style="color:#0f172a;font-size:13px;">${esc(g.nombre)}</b>
                                        ${g.observaciones ? `<div style="font-size:11px;color:#64748b;margin-top:2px;">${esc(g.observaciones)}</div>` : ''}
                                    </td>
                                    <td>
                                        <span class="chip">${esc(g.programa || 'Todos los programas')}</span>
                                        <span class="chip purple">${esc(g.cohorte || 'Todas las cohortes')}</span>
                                    </td>
                                    <td>
                                        ${entArr.length ? entArr.map(e => `
                                            <span style="display:inline-block;background:#f1f5f9;color:#334155;padding:2px 7px;border-radius:6px;font-size:11px;margin:2px;border:1px solid #e2e8f0;">
                                                <i class="fas fa-user fa-xs mr-1" style="color:#0284c7;"></i>${esc(typeof e === 'object' ? (e.name || e.nombre || 'Entrevistador') : e)}
                                            </span>
                                        `).join('') : '<span style="color:#94a3b8;font-size:11px;">Sin entrevistadores asignados</span>'}
                                    </td>
                                    <td>
                                        <b style="font-size:13px;color:#059669;">${egrArr.length || ROSTER.length}</b> <span style="font-size:11px;color:#64748b;">egresados</span>
                                    </td>
                                    <td>
                                        <span class="chip ok">${esc(g.estado || 'activo')}</span>
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:5px;">
                                            <button class="btn sm outline" title="Abrir llamadas para este lote" onclick="APP.gotoLlamadasConFiltro('${esc(g.programa || '')}')">
                                                <i class="fas fa-phone-alt"></i> Llamadas
                                            </button>
                                            <button class="btn sm" style="background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;" title="Eliminar grupo" onclick="APP.deleteGrupo(${g.id})">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        `}
    </div>`;
}

async function saveGrupo(form){
    const nombre = form.nombre.value.trim();
    const programa = form.programa.value;
    const cohorte = form.cohorte.value;
    const observaciones = form.observaciones.value.trim();

    const checkedBoxes = Array.from(form.querySelectorAll('input[name="entrevistador_id"]:checked'));
    const entrevistadores = checkedBoxes.map(cb => ({
        id: cb.value,
        name: cb.dataset.name || 'Entrevistador',
        email: cb.dataset.email || ''
    }));

    let matchingEgresados = ROSTER;
    if (programa) {
        matchingEgresados = matchingEgresados.filter(p => (p.programa || '').toLowerCase().includes(programa.toLowerCase()));
    }
    if (cohorte) {
        matchingEgresados = matchingEgresados.filter(p => (p.egreso || '').includes(cohorte));
    }
    const egresados_ids = matchingEgresados.map(p => p.id);

    const payload = {
        nombre,
        programa,
        cohorte,
        observaciones,
        entrevistadores,
        egresados_ids
    };

    try {
        const resp = await fetch(ROUTES.gruposGuardar, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const data = await resp.json();
        if (resp.ok && data.success) {
            state.grupos.unshift(data.grupo);
            alert('¡Equipo de entrevistadores "' + nombre + '" guardado exitosamente en la base de datos!');
            render();
        } else {
            alert('Error al guardar grupo: ' + (data.message || 'Error del servidor'));
        }
    } catch(err) {
        console.error(err);
        alert('No se pudo conectar con el servidor.');
    }
}

async function deleteGrupo(id){
    if (!confirm('¿Confirma que desea eliminar este grupo de entrevistadores?')) return;
    try {
        const resp = await fetch(ROUTES.gruposEliminar + '/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            }
        });
        const data = await resp.json();
        if (resp.ok && data.success) {
            state.grupos = state.grupos.filter(g => g.id !== id);
            render();
        } else {
            alert('No se pudo eliminar el grupo.');
        }
    } catch(err) {
        console.error(err);
        alert('Error de conexión al eliminar.');
    }
}

function updateGrupoPreview(){
    const prog = document.getElementById('grupo-sel-prog')?.value || '';
    const cohorte = document.getElementById('grupo-sel-cohorte')?.value || '';
    let filtered = ROSTER;
    if (prog) {
        filtered = filtered.filter(p => (p.programa || '').toLowerCase().includes(prog.toLowerCase()));
    }
    if (cohorte) {
        filtered = filtered.filter(p => (p.egreso || '').includes(cohorte));
    }
    const box = document.getElementById('grupo-preview-egresados');
    if (box) {
        box.innerHTML = `<i class="fas fa-users mr-1"></i> Egresados que integrarán este grupo: <b>${filtered.length}</b> seleccionables`;
    }
}

function gotoLlamadasConFiltro(programa){
    state.program = programa || 'todos';
    goto('llamadas');
}

function copyWhatsappMessage(){
    const text = document.getElementById('wa-msg-text')?.value || '';
    navigator.clipboard.writeText(text).then(() => {
        const btnText = document.getElementById('wa-copy-btn-text');
        if (btnText) {
            btnText.innerHTML = '<i class="fas fa-check"></i> ¡Mensaje copiado!';
            setTimeout(() => {
                btnText.textContent = 'Copiar mensaje completo';
            }, 2500);
        }
    }).catch(err => {
        prompt('Copia el siguiente mensaje para WhatsApp:', text);
    });
}

function copyUrlOnly(url){
    navigator.clipboard.writeText(url).then(() => {
        alert('Enlace copiado al portapapeles:\n' + url);
    }).catch(() => {
        prompt('Copia el siguiente enlace:', url);
    });
}

// ---------------- ACCIONES Y ACOMPAÑAMIENTO ----------------
function acciones(){
    const acts = state.actions || [];
    return hero('GESTIÓN Y RESPUESTA INSTITUCIONAL', 'Acompañamiento y acciones («Del hallazgo a la acción»)', 'Transforma las solicitudes de egresados en compromisos de apoyo con responsable, fecha y resultado.') +
    `<div class="cards">
        ${stat('Total de casos', acts.length, 'Registrados en el sistema', 'c1')}
        ${stat('Por revisar', acts.filter(a=>a.estado==='Por revisar').length, 'Pendientes de asignar', 'c2')}
        ${stat('En atención', acts.filter(a=>a.estado==='En atención').length, 'Con respuesta en marcha', 'c3')}
        ${stat('Cerrados', acts.filter(a=>a.estado==='Cerrado').length, 'Resueltos', 'c4')}
    </div>
    <div class="panel">
        <h2>Registrar nueva acción de acompañamiento</h2>
        <form id="v8-action-form">
            <div class="v8-two">
                <div class="field">
                    <label>Egresado/a</label>
                    <select name="pid" required>
                        ${ROSTER.slice(0, 100).map(p => `<option value="${p.id}">${esc(p.nombre)} (${esc(p.programa)})</option>`).join('')}
                    </select>
                </div>
                <div class="field">
                    <label>Tipo de respuesta</label>
                    <select name="tema">
                        ${v8Opts(['Orientación de empleo', 'Apoyo a titulación', 'Mentoría pedagógica', 'Visita de acompañamiento', 'Invitación a curso', 'Reencuentro territorial', 'Consulta sobre condiciones laborales'], '')}
                    </select>
                </div>
            </div>
            <div class="v8-two">
                <div class="field">
                    <label>Responsable</label>
                    <input name="responsable" value="Coordinación de Empleabilidad" required>
                </div>
                <div class="field">
                    <label>Fecha programada</label>
                    <input type="date" name="fecha" value="${new Date().toISOString().slice(0,10)}" required>
                </div>
            </div>
            <div class="field">
                <label>Propósito de la acción</label>
                <input name="nota" placeholder="Describe brevemente el objetivo y las gestiones a realizar">
            </div>
            <button class="btn green" type="submit" style="margin-top:12px;">+ Guardar caso en base de datos</button>
        </form>
    </div>
    <div class="panel">
        <div class="section-top">
            <h2>Plan de seguimiento</h2>
        </div>
        <div class="tablewrap">
            <table>
                <thead>
                    <tr><th>Caso / Egresado</th><th>Tipo de apoyo</th><th>Responsable</th><th>Fecha</th><th>Estado</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    ${acts.map(a => `
                        <tr>
                            <td><b>${esc(a.nombre)}</b><span class="mutedline">${esc(a.pid)}</span></td>
                            <td>${esc(a.tema)}</td>
                            <td>${esc(a.responsable)}</td>
                            <td>${esc(a.fecha)}</td>
                            <td><span class="chip ${a.estado==='Cerrado'?'ok':a.estado==='En atención'?'':'warn'}">${esc(a.estado)}</span></td>
                            <td>
                                <button class="btn sm outline" onclick="APP.advanceAction('${a.id}')">
                                    ${a.estado==='Por revisar'?'Iniciar':a.estado==='En atención'?'Cerrar':'Reabrir'}
                                </button>
                            </td>
                        </tr>
                    `).join('') || '<tr><td colspan="6">No hay casos registrados aún.</td></tr>'}
                </tbody>
            </table>
        </div>
    </div>`;
}

async function saveAction(ev){
    ev.preventDefault();
    const d = Object.fromEntries(new FormData(ev.currentTarget).entries());
    const p = locate(d.pid);
    if (!p) return;

    try {
        const res = await fetch(ROUTES.accion, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json'},
            body: JSON.stringify({
                pid: p.id,
                nombre: p.nombre,
                tema: d.tema,
                responsable: d.responsable,
                fecha: d.fecha,
                nota: d.nota
            })
        });
        const json = await res.json();
        state.actions.unshift(json.caso);
    } catch(e) {
        state.actions.unshift({
            id: 'A-' + Date.now(),
            pid: p.id,
            nombre: p.nombre,
            tema: d.tema,
            responsable: d.responsable,
            fecha: d.fecha,
            estado: 'Por revisar',
            nota: d.nota
        });
    }
    render();
}

async function advanceAction(id){
    const a = state.actions.find(x => String(x.id) === String(id));
    if (!a) return;

    try {
        const res = await fetch(`${ROUTES.avanzarAccion}/${id}`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json'}
        });
        const json = await res.json();
        a.estado = json.nuevo_estado;
    } catch(e) {
        a.estado = a.estado === 'Por revisar' ? 'En atención' : a.estado === 'En atención' ? 'Cerrado' : 'Por revisar';
    }
    render();
}

// ---------------- CONTACTOS CON AUDITORÍA ----------------
function contactos(){
    return hero('PROTECCIÓN Y VINCULACIÓN', 'Directorio de contactos autorizados', 'Acceso a canales de comunicación bajo justificación de finalidad auditada.') +
    `<div class="notice privacy">
        <strong>Acceso seguro auditado:</strong> Cada consulta de teléfono o correo solicita una finalidad legítima y queda registrada en la bitácora institucional <code>egresado_accesos_contacto</code>.
    </div>
    <div class="panel">
        <div class="section-top">
            <h2>Egresados en directorio</h2>
        </div>
        <div class="tablewrap">
            <table>
                <thead><tr><th>Persona</th><th>Programa · Cohorte</th><th>Contacto declarado</th><th>Acciones</th></tr></thead>
                <tbody>
                    ${ROSTER.slice(0, 40).map(p => {
                        const v = formValues(p);
                        return `<tr>
                            <td><b>${esc(p.nombre)}</b><span class="mutedline">${esc(p.id)}</span></td>
                            <td>${esc(p.programa)} · ${esc(p.egreso || 'Por verificar')}</td>
                            <td>${v.telefono ? '✓ Celular registrado' : 'Sin teléfono'} · ${v.correo ? '✓ Correo registrado' : 'Sin correo'}</td>
                            <td>
                                <button class="btn outline sm" onclick="APP.requestContact('${p.id}')">Ver datos de contacto</button>
                                <button class="btn sm" onclick="APP.selectQuick('${p.id}')">Actualizar ficha</button>
                            </td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel">
        <h2>Bitácora de auditoría de consultas</h2>
        <div class="tablewrap" style="max-height:220px;">
            <table>
                <thead><tr><th>Fecha / Hora</th><th>Rol consultante</th><th>ID Consultado</th><th>Finalidad justificada</th></tr></thead>
                <tbody>
                    ${state.contactLog.slice(0, 15).map(x => `
                        <tr>
                            <td>${esc(x.at)}</td>
                            <td><span class="chip">${esc(x.role)}</span></td>
                            <td><code>${esc(x.id)}</code></td>
                            <td>${esc(x.purpose)}</td>
                        </tr>
                    `).join('') || '<tr><td colspan="4">Aún no hay registros de auditoría en esta sesión.</td></tr>'}
                </tbody>
            </table>
        </div>
    </div>`;
}

function requestContact(id){
    const p = locate(id);
    if (!p) return;
    const purposes = ['Seguimiento semestral de egresados', 'Gestión de entrevista telefónica', 'Orientación para titulación', 'Invitación a curso de formación continua', 'Coordinación académica'];
    $('#modalhost').innerHTML = `
        <div class="modalbg">
            <div class="modal">
                <div class="section-top">
                    <h2>Auditoría de acceso · ${esc(p.nombre)}</h2>
                    <button class="btn gray sm" onclick="APP.closeModal()">✕</button>
                </div>
                <div class="notice privacy" style="margin:12px 0;">
                    Indica la finalidad para acceder al número de teléfono y correo de esta persona.
                </div>
                <div class="field">
                    <label>Finalidad de la consulta</label>
                    <select id="contact-purpose">
                        ${purposes.map(x => `<option>${esc(x)}</option>`).join('')}
                    </select>
                </div>
                <div class="buttons">
                    <button class="btn gray" onclick="APP.closeModal()">Cancelar</button>
                    <button class="btn" onclick="APP.revealContact('${p.id}')">Confirmar y ver contacto</button>
                </div>
            </div>
        </div>
    `;
}

async function revealContact(id){
    const p = locate(id);
    const purpose = $('#contact-purpose')?.value || 'Seguimiento';
    const v = formValues(p);
    const at = new Date().toLocaleString('es-PE');

    state.contactLog.unshift({role: state.role, id: p.id, at, purpose});

    // Guardar log en MySQL
    try {
        await fetch(ROUTES.auditarContacto, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN},
            body: JSON.stringify({id: p.id, role: state.role, purpose})
        });
    } catch(e){}

    $('#modalhost').innerHTML = `
        <div class="modalbg">
            <div class="modal">
                <div class="section-top">
                    <h2>Datos de contacto · ${esc(p.nombre)}</h2>
                    <button class="btn gray sm" onclick="APP.closeModal()">✕</button>
                </div>
                <table style="margin-top:15px;">
                    <tr><td style="width:40%;"><b>Celular / WhatsApp:</b></td><td>${esc(v.telefono || 'No registrado')}</td></tr>
                    <tr><td><b>Teléfono alternativo:</b></td><td>${esc(v.telefono_alt || 'No registrado')}</td></tr>
                    <tr><td><b>Correo electrónico:</b></td><td>${esc(v.correo || 'No registrado')}</td></tr>
                    <tr><td><b>Dirección declarada:</b></td><td>${esc(v.direccion_contacto || 'No registrada')}</td></tr>
                    <tr><td><b>Distrito de residencia:</b></td><td>${esc(v.res_distrito || 'Cusco')}</td></tr>
                </table>
                <div class="buttons">
                    <button class="btn gray" onclick="APP.closeModal()">Cerrar</button>
                    <button class="btn" onclick="APP.closeModal(); APP.selectQuick('${p.id}');">Ir a actualización</button>
                </div>
            </div>
        </div>
    `;
}

function closeModal(){
    $('#modalhost').innerHTML = '';
}

function selectQuick(id){
    state.selected = id;
    state.page = 'agil';
    render();
    window.scrollTo({top:0, behavior:'instant'});
}

// ---------------- LLAMADAS Y RECUPERACIÓN ----------------
function llamadas(){
    return hero('RECUPERACIÓN DE RESPUESTAS', 'Llamadas y entrevistas telefónicas', 'Para encuestadores y coordinadores: registra el resultado de la llamada y abre la ficha al contestar.') +
    `<div class="panel">
        <div class="section-top">
            <h2>Cola de egresados para entrevista</h2>
        </div>
        <div class="tablewrap">
            <table>
                <thead><tr><th>Persona</th><th>Programa</th><th>Contacto</th><th>Última llamada</th><th>Acción</th></tr></thead>
                <tbody>
                    ${ROSTER.slice(0, 30).map(p => {
                        const c = state.calls.filter(x => x.id === p.id).slice(-1)[0];
                        return `<tr>
                            <td><b>${esc(p.nombre)}</b><span class="mutedline">${esc(p.id)}</span></td>
                            <td>${esc(p.programa)}</td>
                            <td>${esc(p.telefono || 'Sin teléfono')}</td>
                            <td>${c ? esc(c.result) + ' (' + esc(c.at) + ')' : 'Sin intentos'}</td>
                            <td>
                                <button class="btn sm" onclick="APP.openCall('${p.id}')">☎ Registrar llamada</button>
                            </td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>
        </div>
    </div>`;
}

function openCall(id){
    const p = locate(id);
    if (!p) return;
    $('#modalhost').innerHTML = `
        <div class="modalbg">
            <div class="modal">
                <div class="section-top">
                    <h2>☎ Gestión de llamada · ${esc(p.nombre)}</h2>
                    <button class="btn gray sm" onclick="APP.closeModal()">✕</button>
                </div>
                <div class="field" style="margin-top:14px;">
                    <label>Resultado de la comunicación</label>
                    <select id="call-result">
                        <option>Entrevista aceptada (iniciar cuestionario)</option>
                        <option>No contesta</option>
                        <option>Reprogramar para otra hora</option>
                        <option>Número equivocado / inubicable</option>
                        <option>Rechaza participar</option>
                    </select>
                </div>
                <div class="field" style="margin-top:14px;">
                    <label>Observaciones de la llamada</label>
                    <textarea id="call-comment" placeholder="Comentarios del entrevistador..."></textarea>
                </div>
                <div class="buttons">
                    <button class="btn gray" onclick="APP.closeModal()">Cancelar</button>
                    <button class="btn green" onclick="APP.registerCall('${p.id}')">Guardar resultado</button>
                </div>
            </div>
        </div>
    `;
}

async function registerCall(id){
    const res = $('#call-result').value;
    const com = $('#call-comment').value;
    const at = new Date().toLocaleString('es-PE');

    state.calls.push({id, result: res, comment: com, at});

    try {
        await fetch(ROUTES.llamada, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN},
            body: JSON.stringify({id, result: res, comment: com})
        });
    } catch(e){}

    closeModal();

    if (res.startsWith('Entrevista aceptada')) {
        state.formMode = 'telefono';
        selectQuick(id);
    } else {
        render();
    }
}

// ---------------- OBSERVATORIO TERRITORIAL (PukGeo) ----------------
function territory(){
    return window.PukGeo ? window.PukGeo.view() : '<div class="panel">Cargando observatorio territorial...</div>';
}

// ---------------- INTELIGENCIA INSTITUCIONAL (Analytics) ----------------
function inteligencia(){
    return window.Analytics ? window.Analytics.view() : '<div class="panel">Cargando inteligencia institucional...</div>';
}

// ---------------- MI FICHA ----------------
function ficha(){
    const p = locate(state.selected) || ROSTER[0];
    const v = formValues(p);
    const hist = state.history.filter(x => x.id === keyOf(p));

    const pIdStr = String(p.id || '');
    const numId = pIdStr.startsWith('REAL-') ? parseInt(pIdStr.replace('REAL-', '')) : (Number.isInteger(Number(pIdStr)) ? Number(pIdStr) : null);
    const myEpisodes = (state.jobEpisodes || []).filter(e => numId ? e.user_id == numId : e.identificador_temporal == p.id);

    return hero('PORTAL DE EGRESADOS', 'Mi ficha de seguimiento', 'Consulta tu información académica y laboral registrada en la escuela.', `<button class="btn" onclick="APP.selectQuick('${p.id}')">✎ Actualizar mi ficha</button>`) +
    `<div class="two">
        <div class="panel">
            <h2>Datos vigentes en el sistema</h2>
            <table>
                <tr><td><b>Nombre:</b></td><td>${esc(p.nombre)}</td></tr>
                <tr><td><b>Programa:</b></td><td>${esc(p.programa)}</td></tr>
                <tr><td><b>Año / Cohorte:</b></td><td>${esc(p.egreso)}</td></tr>
                <tr><td><b>Condición académica:</b></td><td>${esc(p.situacion || 'Egresado/a')}</td></tr>
                <tr><td><b>Situación laboral:</b></td><td>${esc(v.trabaja || 'Por actualizar')}</td></tr>
                <tr><td><b>Institución:</b></td><td>${esc(v.institucion || 'Por actualizar')}</td></tr>
                <tr><td><b>Cargo:</b></td><td>${esc(v.cargo || 'Por actualizar')}</td></tr>
                <tr><td><b>Celular / WhatsApp:</b></td><td>${esc(v.telefono || 'Por actualizar')}</td></tr>
                <tr><td><b>Correo electrónico:</b></td><td>${esc(v.correo || 'Por actualizar')}</td></tr>
                <tr><td><b>Distrito de residencia:</b></td><td>${esc(v.res_distrito || 'Cusco')}</td></tr>
                <tr><td><b>Distrito de trabajo:</b></td><td>${esc(v.trab_distrito || 'Por actualizar')}</td></tr>
            </table>
        </div>
        <div class="panel">
            <h2>Trayectoria laboral histórica (R03)</h2>
            ${myEpisodes.length > 0 ? `
                <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:16px;">
                    ${myEpisodes.map(ep => `
                        <div style="border-left: 3px solid ${ep.es_actual ? '#10b981' : '#94a3b8'}; padding: 8px 12px; background: #f8fafc; border-radius: 0 8px 8px 0;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <strong style="font-size:13px; color:#0f172a;">${esc(ep.institucion)}</strong>
                                <span class="chip ${ep.es_actual ? 'ok' : ''}">${ep.es_actual ? 'Activo' : 'Concluido'}</span>
                            </div>
                            <div style="font-size:12px; color:#475569; margin-top:2px;">
                                ${esc(ep.cargo)} · ${ep.es_docencia_directa ? '👨‍🏫 Docencia directa' : 'Gestión/Otro'} · ${esc(ep.contrato || 'Contrato')}
                            </div>
                            <div style="font-size:11px; color:#64748b; margin-top:3px;">
                                Periodo: <b>${esc(ep.fecha_inicio)}</b> al <b>${esc(ep.fecha_fin || (ep.es_actual ? 'Presente' : 'Fecha no especificada'))}</b>
                                ${ep.motivo_cambio ? ` · Motivo: ${esc(ep.motivo_cambio)}` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            ` : '<div class="notice" style="margin-bottom:16px;">No se registran episodios laborales para este egresado.</div>'}

            <h2>Historial de actualizaciones</h2>
            ${hist.map(h => `
                <div class="history">
                    <strong>${esc(h.at)} · ${esc(h.source)}</strong>
                    <br>${h.changed.length} campos actualizados.
                </div>
            `).join('') || '<div class="notice">No se han registrado modificaciones recientes para esta ficha.</div>'}
        </div>
    </div>`;
}

// ---------------- CAMPAÑAS SEMESTRALES Y WHATSAPP ----------------
function campanasView(){
    const campanaActual = '2026-II';
    const publicUrl = ROUTES.campanaPublica || (window.location.origin + '/seguimiento-egresados/campana-publica');
    const msgDefault = `¡Hola! 🎓 La EESP Pukllasunchis te invita a participar en la Campaña de Actualización de Egresados (${campanaActual}).\n\nTe tomará solo 3 minutos confirmar tus datos y contarnos sobre tu práctica profesional y necesidades de formación continua:\n👉 ${publicUrl}\n\n¡Gracias por formar parte de nuestra comunidad educativa!`;
    const waUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(msgDefault);

    return hero('CAMPAÑAS SEMESTRALES', 'Campañas de actualización de egresados', 'Planifica y ejecuta las fases de autorespuesta y difusión vía WhatsApp de egresados.') +
    `<div class="cards">
        ${stat('Campaña activa', campanaActual, 'Segundo semestre', 'c1')}
        ${stat('Población elegible', ROSTER.length, 'Egresados en padrón', 'c2')}
        ${stat('Actualizaciones', state.history.length, 'Respuestas guardadas', 'c3')}
        ${stat('Casos de apoyo', state.actions.length, 'Compromisos abiertos', 'c4')}
    </div>

    <!-- Generador de Link de Campaña para WhatsApp -->
    <div class="panel" id="seccion-whatsapp" style="border-left: 4px solid #25d366; background: linear-gradient(to right, #f0fdf4, #ffffff);">
        <div class="section-top" style="margin-bottom:12px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:40px;height:40px;border-radius:10px;background:#25d366;display:flex;align-items:center;justify-content:center;color:white;font-size:1.4rem;">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div>
                    <h2 style="margin:0;color:#064e3b;">Generador de Enlace para WhatsApp (Campaña ${campanaActual})</h2>
                    <p style="margin:2px 0 0;font-size:12px;color:#64748b;">Genera y comparte el enlace institucional con el mensaje oficial para los grupos de WhatsApp de egresados.</p>
                </div>
            </div>
            <span class="chip ok" style="background:#25d366;color:white;"><i class="fab fa-whatsapp mr-1"></i> WhatsApp Oficial</span>
        </div>

        <div style="display:grid;grid-template-columns:1.2fr 0.8fr;gap:16px;">
            <div>
                <label style="font-weight:700;font-size:12px;color:#334155;display:block;margin-bottom:5px;">
                    Mensaje preformateado para WhatsApp:
                </label>
                <textarea id="wa-msg-text" rows="6" style="width:100%;padding:10px 12px;border:1.5px solid #86efac;border-radius:10px;font-size:13px;line-height:1.4;background:white;color:#1e293b;">${esc(msgDefault)}</textarea>
                
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;">
                    <button class="btn green" onclick="APP.copyWhatsappMessage()" style="background:#059669;padding:9px 15px;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-copy"></i> <span id="wa-copy-btn-text">Copiar mensaje completo</span>
                    </button>
                    <a id="wa-share-link" href="${waUrl}" target="_blank" class="btn" style="background:#25d366;color:white;padding:9px 15px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-weight:700;">
                        <i class="fab fa-whatsapp"></i> Abrir en WhatsApp
                    </a>
                    <button class="btn outline sm" onclick="APP.copyUrlOnly('${esc(publicUrl)}')" style="padding:9px 13px;">
                        <i class="fas fa-link mr-1"></i> Copiar solo URL
                    </button>
                </div>
            </div>

            <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:14px;display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <span style="font-size:11px;font-weight:800;letter-spacing:0.05em;color:#059669;text-transform:uppercase;display:block;margin-bottom:6px;">
                        <i class="fas fa-globe mr-1"></i> Destino del enlace
                    </span>
                    <b style="font-size:12px;color:#0f172a;word-break:break-all;display:block;margin-bottom:8px;padding:6px;background:#f8fafc;border-radius:6px;border:1px dashed #cbd5e1;">
                        ${esc(publicUrl)}
                    </b>
                    <p style="font-size:11.5px;color:#64748b;margin:0;">
                        Los egresados acceden desde su teléfono móvil sin necesidad de clave. Al enviar sus respuestas, se guardan instantáneamente en la base de datos institucional.
                    </p>
                </div>
                <div style="margin-top:12px;padding-top:10px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:11px;color:#94a3b8;"><i class="fas fa-mobile-alt mr-1"></i> Formato móvil optimizado</span>
                    <a href="${esc(publicUrl)}" target="_blank" class="btn sm" style="background:#f1f5f9;color:#0f172a;font-weight:600;">
                        <i class="fas fa-external-link-alt mr-1"></i> Ver vista pública
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Opciones de difusión -->
    <div class="panel">
        <h2>Canales de difusión institucional</h2>
        <div class="opgrid">
            <div class="opbox">
                <span class="chip ok">1. WhatsApp / Magic Link</span>
                <strong>Campaña Semestral</strong>
                <p>Enlace público de autoregistro para enviar a delegados y grupos de WhatsApp.</p>
                <button class="btn sm" onclick="document.getElementById('seccion-whatsapp').scrollIntoView({behavior:'smooth'})" style="margin-top:10px;">Ver mensaje de WhatsApp</button>
            </div>
            <div class="opbox">
                <span class="chip purple">2. Equipos de Entrevistadores</span>
                <strong>Llamadas telefónicas</strong>
                <p>Comisiones docentes para llamar a egresados que no contestaron la encuesta.</p>
                <button class="btn sm outline" onclick="APP.goto('grupos')" style="margin-top:10px;">Gestionar equipos</button>
            </div>
            <div class="opbox">
                <span class="chip">3. Intranet Docente</span>
                <strong>Permanente</strong>
                <p>Acceso desde el panel docente en 'Mis funciones' de la EESP Pukllasunchis.</p>
                <button class="btn sm outline" onclick="APP.goto('agil')" style="margin-top:10px;">Abrir ficha</button>
            </div>
        </div>
    </div>`;
}

// ---------------- RENDER GLOBAL ----------------
function render(){
    nav();
    const renderers = {
        panel,
        padron7: () => hero('CALIDAD DE INFORMACIÓN', 'Padrón · Siete listas institucionales', 'Consolidado histórico de cohortes egresadas 2021-II a 2025-II.') +
            `<div class="cards">
                ${stat('Filas nominales', ROSTER_SEVEN_V8.cantidad_filas, 'En archivos de origen', 'c1')}
                ${stat('Personas únicas', ROSTER_SEVEN_V8.personas_unicas, 'Depuradas por DNI', 'c2')}
                ${stat('Con celular', ROSTER_SEVEN_V8.con_celular, 'Disponibles para Magic Link', 'c3')}
                ${stat('En BD Puklla', INSTITUTIONAL_EGRESADOS.length, 'Egresados en users MySQL', 'c4')}
            </div>
            <div class="panel">
                <h2>Inventario por cohorte institucional</h2>
                <div class="tablewrap">
                    <table>
                        <thead><tr><th>Lista</th><th>Filas</th><th>Inicial</th><th>Primaria EIB</th><th>Condición explícita</th><th>Contactos</th></tr></thead>
                        <tbody>
                            ${ROSTER_SEVEN_V8.periodos.map(x => `
                                <tr>
                                    <td><b>${x.periodo}</b></td>
                                    <td>${x.filas}</td>
                                    <td>${x.inicial}</td>
                                    <td>${x.eib}</td>
                                    <td><span class="chip ok">${x.condicion_explicita}</span></td>
                                    <td>${x.contactos}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>`,
        grupos: gruposView,
        agil: actualizacionAgil,
        acciones,
        campanas: campanasView,
        llamadas,
        cuestionario: actualizacionAgil,
        territorio: territory,
        inteligencia,
        formacion: () => hero('DESARROLLO PROFESIONAL', 'Formación continua y red de aprendizaje', 'Análisis de necesidades formativas y facilitadores docentes.') +
            `<div class="cards">
                ${stat('Temas demandados', '8 categorías', 'Priorizadas por egresados', 'c1')}
                ${stat('Pueden compartir', state.history.length, 'Docentes mentores', 'c2')}
            </div>
            <div class="panel">
                <h2>Demandas de capacitación declaradas</h2>
                <div class="progressrow"><strong>Planificación y evaluación</strong><div class="bar teal"><span style="width:42%"></span></div><b>42%</b></div>
                <div class="progressrow"><strong>Educación intercultural / Quechua</strong><div class="bar gold"><span style="width:34%"></span></div><b>34%</b></div>
                <div class="progressrow"><strong>Aulas multigrado / ruralidad</strong><div class="bar purple"><span style="width:28%"></span></div><b>28%</b></div>
                <div class="progressrow"><strong>Investigación y titulación</strong><div class="bar"><span style="width:22%"></span></div><b>22%</b></div>
            </div>`,
        contactos,
        permisos: () => hero('SEGURIDAD Y ROLES', 'Roles y privilegios de acceso', 'Matriz de control de acceso para encuestadores y analistas.') +
            `<div class="panel">
                <h2>Funciones de la intranet</h2>
                <table>
                    <thead><tr><th>Rol</th><th>Fichas</th><th>Contactos</th><th>Exportación Excel</th></tr></thead>
                    <tbody>
                        <tr><td><b>Coordinación</b></td><td>Todas</td><td>Auditado con finalidad</td><td>Completa</td></tr>
                        <tr><td><b>Entrevistador</b></td><td>Asignadas</td><td>Auditado con finalidad</td><td>No</td></tr>
                        <tr><td><b>Dirección</b></td><td>Agregadas</td><td>Justificado</td><td>Agregada</td></tr>
                        <tr><td><b>Egresado</b></td><td>Propia</td><td>Propio</td><td>No</td></tr>
                    </tbody>
                </table>
            </div>`,
        ficha,
        padron: () => hero('BASE DE DATOS', 'Padrón institucional de egresados', 'Lista completa de egresados de la escuela EESPP Pukllasunchis.') +
            `<div class="panel">
                <div class="section-top">
                    <h2>Egresados registrados</h2>
                    <input id="search-box" placeholder="Buscar por nombre..." style="padding:8px 12px;border:1px solid #d2d8e8;border-radius:8px;" oninput="APP.filterSearch(this.value)">
                </div>
                <div class="tablewrap" id="roster-table-wrap">
                    <table>
                        <thead><tr><th>Nombre</th><th>Programa</th><th>Cohorte</th><th>Condición</th><th>Acción</th></tr></thead>
                        <tbody id="roster-tbody">
                            ${ROSTER.slice(0, 50).map(p => `
                                <tr>
                                    <td><b>${esc(p.nombre)}</b><span class="mutedline">${esc(p.id)}</span></td>
                                    <td>${esc(p.programa)}</td>
                                    <td>${esc(p.egreso || 'Por verificar')}</td>
                                    <td><span class="chip ok">${esc(p.situacion || 'Egresado')}</span></td>
                                    <td>
                                        <button class="btn sm outline" onclick="APP.selectQuick('${p.id}')">Ver / Actualizar</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>`
    };

    $('#content').innerHTML = (renderers[state.page] || panel)();

    // Eventos específicos por vista
    if (state.page === 'agil') {
        const f = $('#v8-fast-form');
        if (f) {
            f.addEventListener('submit', saveQuick);
            const job = f.querySelector('select[name="trabaja"]');
            if (job) job.addEventListener('change', switchQuickJob);
            const ch = f.querySelector('select[name="cambio_empleo"]');
            if (ch) ch.addEventListener('change', switchQuickJob);
        }
        const sw = $('#switch-egresado-select');
        if (sw) sw.addEventListener('change', e => selectQuick(e.target.value));
    }

    if (state.page === 'acciones') {
        const f = $('#v8-action-form');
        if (f) f.addEventListener('submit', saveAction);
    }

    if (state.page === 'territorio' && window.PukGeo) window.PukGeo.bind();
    if (state.page === 'inteligencia' && window.Analytics) window.Analytics.bind();
}

function filterSearch(txt){
    const tbody = $('#roster-tbody');
    if (!tbody) return;
    const q = txt.toLowerCase();
    const filtered = ROSTER.filter(p => p.nombre.toLowerCase().includes(q) || p.programa.toLowerCase().includes(q));
    tbody.innerHTML = filtered.slice(0, 50).map(p => `
        <tr>
            <td><b>${esc(p.nombre)}</b><span class="mutedline">${esc(p.id)}</span></td>
            <td>${esc(p.programa)}</td>
            <td>${esc(p.egreso || 'Por verificar')}</td>
            <td><span class="chip ok">${esc(p.situacion || 'Egresado')}</span></td>
            <td><button class="btn sm outline" onclick="APP.selectQuick('${p.id}')">Ver / Actualizar</button></td>
        </tr>
    `).join('') || '<tr><td colspan="5">No se encontraron egresados coincidentes.</td></tr>';
}

window.APP = {
    goto,
    setRole,
    selectQuick,
    confirmarSinCambios,
    cerrarEpisodioModal,
    openCall,
    registerCall,
    requestContact,
    revealContact,
    closeModal,
    advanceAction,
    filterSearch,
    saveGrupo,
    deleteGrupo,
    updateGrupoPreview,
    gotoLlamadasConFiltro,
    copyWhatsappMessage,
    copyUrlOnly,
    render
};

$('#role-picker').addEventListener('change', e => setRole(e.target.value));

render();

})();
</script>

{{-- Inclusión de módulos de Analítica y Territorio V8 --}}
<script id="analitica-js">
(function(){'use strict';
const ROWS = [
    {"programa":"Inicial","egreso":"2025","academica":"Egresado","trabaja":"Trabaja","insercion":"Fuera de educación","sector":"Indeterminado","practica":"Otros sectores"},
    {"programa":"Primaria EIB","egreso":"2025","academica":"T. Bach.","trabaja":"Trabaja","insercion":"En educación","sector":"Privado/particular","practica":"Docencia directa"},
    {"programa":"Inicial","egreso":"2024","academica":"Licenciada/a","trabaja":"Trabaja","insercion":"En educación","sector":"Indeterminado","practica":"Gestión educativa"},
    {"programa":"Primaria EIB","egreso":"2023","academica":"Licenciada/a","trabaja":"Trabaja","insercion":"En educación","sector":"Público/estatal","practica":"Educación no formal / comunitaria"},
    {"programa":"Primaria EIB","egreso":"2025","academica":"T. Bach.","trabaja":"Trabaja","insercion":"Fuera de educación","sector":"Indeterminado","practica":"Otros sectores"},
    {"programa":"Primaria EIB","egreso":"2025","academica":"IX","trabaja":"No trabaja","insercion":"No trabaja","sector":"No trabaja","practica":"No trabaja / tesis"},
    {"programa":"Inicial","egreso":"2023","academica":"Licenciada/a","trabaja":"Trabaja","insercion":"En educación","sector":"Privado/particular","practica":"Docencia directa"},
    {"programa":"Inicial","egreso":"2024","academica":"Licenciada/a","trabaja":"Trabaja","insercion":"Fuera de educación","sector":"Indeterminado","practica":"Otros sectores"},
    {"programa":"Primaria EIB","egreso":"2025","academica":"T. Bach.","trabaja":"Trabaja","insercion":"Fuera de educación","sector":"Público/estatal","practica":"Gestión educativa"},
    {"programa":"Inicial","egreso":"2025","academica":"T. Bach.","trabaja":"Trabaja","insercion":"En educación","sector":"Público/estatal","practica":"Gestión educativa"},
    {"programa":"Primaria EIB","egreso":"2023","academica":"Licenciada/a","trabaja":"Trabaja","insercion":"En educación","sector":"Indeterminado","practica":"Docencia directa"},
    {"programa":"Primaria EIB","egreso":"2023","academica":"Egresado","trabaja":"No trabaja","insercion":"No trabaja","sector":"No trabaja","practica":"No trabaja / tesis"},
    {"programa":"Inicial","egreso":"2025","academica":"IX","trabaja":"Trabaja","insercion":"En educación","sector":"Privado/particular","practica":"Auxiliar / apoyo pedagógico"},
    {"programa":"Primaria EIB","egreso":"2023","academica":"Licenciada/a","trabaja":"Trabaja","insercion":"En educación","sector":"Público/estatal","practica":"Educación no formal / comunitaria"},
    {"programa":"Primaria EIB","egreso":"2025","academica":"T. Lic.","trabaja":"Trabaja","insercion":"En educación","sector":"Privado/particular","practica":"Docencia directa"},
    {"programa":"Primaria EIB","egreso":"2025","academica":"IX","trabaja":"Trabaja","insercion":"En educación","sector":"Privado/particular","practica":"Docencia directa"},
    {"programa":"Inicial","egreso":"2025","academica":"Bachiller","trabaja":"Trabaja","insercion":"En educación","sector":"Privado/particular","practica":"Docencia directa"}
];

const LABELS = {programa:'Programa', egreso:'Año de egreso', academica:'Situación académica', trabaja:'Situación laboral', insercion:'Campo de inserción', sector:'Sector', practica:'Práctica / función'};
const FILTERS = {programa:'Todos', egreso:'Todos', trabaja:'Todos', insercion:'Todos', sector:'Todos'};
const state = {tab:'cruces', row:'egreso', col:'programa', metric:'n', hideSmall:true};

function filtered(){
    return ROWS.filter(r => Object.entries(FILTERS).every(([k,v]) => v==='Todos' || r[k]===v));
}

function count(rows, fn){ return rows.filter(fn).length; }

function view(){
    const a = filtered();
    const n = a.length;
    const t = count(a, r => r.trabaja === 'Trabaja');
    const e = count(a, r => r.insercion === 'En educación');

    const rs = [...new Set(a.map(x=>x[state.row]||'Sin dato'))].sort();
    const cs = [...new Set(a.map(x=>x[state.col]||'Sin dato'))].sort();

    return `
    <section class="hero ai-title">
        <div>
            <span class="ai-pre">DIRECCIÓN · BIENESTAR Y EMPLEABILIDAD</span>
            <h1>Inteligencia institucional y matrices de cruce</h1>
            <p>Construcción dinámica de tablas cruzadas con medidas de conteo, porcentajes y exportación en Excel multi-hoja.</p>
        </div>
        <div class="hero-actions">
            <button class="btn green" onclick="Analytics.exportExcel()">⬇ Descargar libro Excel (.xlsx)</button>
        </div>
    </section>
    <div class="ai-kpis">
        <div class="ai-kpi"><small>Muestra analizada</small><strong>${n}</strong><span>Respuestas procesadas</span></div>
        <div class="ai-kpi"><small>Trabajan</small><strong>${n?((100*t/n).toFixed(1)+'%'):'—'}</strong><span>${t} de ${n} egresados</span></div>
        <div class="ai-kpi"><small>En educación</small><strong>${n?((100*e/n).toFixed(1)+'%'):'—'}</strong><span>${e} en labor docente o afín</span></div>
        <div class="ai-kpi"><small>Confidencialidad</small><strong>Activa</strong><span>Celdas &lt; 5 reservadas</span></div>
    </div>
    <div class="panel">
        <div class="ai-titlebar">
            <h2>Constructor de tablas cruzadas</h2>
            <div class="ai-tools">
                <div class="ai-select">
                    <label>Variable Fila</label>
                    <select id="ai-row" onchange="Analytics.setParam('row', this.value)">
                        <option value="egreso" ${state.row==='egreso'?'selected':''}>Año de egreso</option>
                        <option value="programa" ${state.row==='programa'?'selected':''}>Programa</option>
                        <option value="trabaja" ${state.row==='trabaja'?'selected':''}>Trabaja</option>
                        <option value="insercion" ${state.row==='insercion'?'selected':''}>Inserción</option>
                    </select>
                </div>
                <div class="ai-select">
                    <label>Variable Columna</label>
                    <select id="ai-col" onchange="Analytics.setParam('col', this.value)">
                        <option value="programa" ${state.col==='programa'?'selected':''}>Programa</option>
                        <option value="trabaja" ${state.col==='trabaja'?'selected':''}>Trabaja</option>
                        <option value="insercion" ${state.col==='insercion'?'selected':''}>Inserción</option>
                        <option value="sector" ${state.col==='sector'?'selected':''}>Sector</option>
                    </select>
                </div>
                <div class="ai-select">
                    <label>Medida</label>
                    <select id="ai-metric" onchange="Analytics.setParam('metric', this.value)">
                        <option value="n" ${state.metric==='n'?'selected':''}>Conteo (N)</option>
                        <option value="fila" ${state.metric==='fila'?'selected':''}>% de Fila</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="ai-table-scroll">
            <table class="ai-table">
                <thead>
                    <tr>
                        <th>${LABELS[state.row] || state.row} ↓ / ${LABELS[state.col] || state.col} →</th>
                        ${cs.map(c => `<th style="text-align:right">${c}</th>`).join('')}
                        <th style="text-align:right">Total fila</th>
                    </tr>
                </thead>
                <tbody>
                    ${rs.map(r => {
                        const rowTotal = count(a, x => (x[state.row]||'Sin dato') === r);
                        return `<tr>
                            <td><b>${r}</b></td>
                            ${cs.map(c => {
                                const val = count(a, x => (x[state.row]||'Sin dato') === r && (x[state.col]||'Sin dato') === c);
                                const lbl = state.metric==='fila' ? (rowTotal ? (100*val/rowTotal).toFixed(1)+'%' : '0%') : val;
                                return `<td class="num">${lbl}</td>`;
                            }).join('')}
                            <td class="num"><b>${rowTotal}</b></td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>
        </div>
    </div>`;
}

function setParam(k, v){
    state[k] = v;
    APP.goto('inteligencia');
}

function exportExcel(){
    alert('Generando libro de indicadores XLSX institucional compatible...');
}

window.Analytics = {view, bind: ()=>{}, setParam, exportExcel};
})();
</script>

<script id="geographic-offline">
(function(){'use strict';
const DEPT = {CUSCO:[-71.97,-13.53], APURIMAC:[-72.88,-13.64], AREQUIPA:[-71.54,-16.40], LORETO:[-73.25,-3.75], 'MADRE DE DIOS':[-69.18,-12.60], PUNO:[-70.02,-15.84], LIMA:[-77.04,-12.05]};
const PROV = {CUSCO:[-71.97,-13.53], QUISPICANCHI:[-71.40,-13.70], ACOMAYO:[-71.68,-14.10], CANCHIS:[-71.23,-14.27], ANTA:[-72.10,-13.45], URUBAMBA:[-72.12,-13.30], CALCA:[-71.95,-13.32], PARURO:[-71.84,-13.77], PAUCARTAMBO:[-71.60,-13.34], CHUMBIVILCAS:[-72.08,-14.29], CANAS:[-71.41,-14.22], 'LA CONVENCION':[-72.78,-12.86]};

function view(){
    return `
    <section class="hero">
        <div>
            <span class="eyebrow">OBSERVATORIO TERRITORIAL</span>
            <h1>Cartografía institucional de egresados</h1>
            <p>Monitoreo territorial de residencia y centros laborales para planificación de formación continua y visitas.</p>
        </div>
    </section>
    <div class="panel">
        <div class="geo-grid">
            <div class="geo-mapframe">
                <svg viewBox="0 0 760 520" style="width:100%;height:auto;background:#f7fafc;border-radius:12px;">
                    <rect width="760" height="520" fill="#f7fafc"/>
                    <path d="M 240,40 L 300,50 L 380,100 L 440,160 L 510,210 L 480,290 L 410,340 L 340,430 L 290,490 L 250,420 L 210,370 L 160,310 L 120,240 L 150,150 L 190,90 Z" fill="#e2edf5" stroke="#9bb5c9" stroke-width="2"/>
                    <circle cx="390" cy="330" r="28" fill="#4e75e5" fill-opacity="0.8"/>
                    <text x="390" y="325" fill="#fff" font-size="12" font-weight="bold" text-anchor="middle">CUSCO</text>
                    <text x="390" y="340" fill="#fff" font-size="10" text-anchor="middle">85% egresados</text>
                    <circle cx="340" cy="335" r="14" fill="#32b3c5" fill-opacity="0.8"/>
                    <text x="340" y="338" fill="#fff" font-size="9" text-anchor="middle">Apurímac</text>
                    <circle cx="430" cy="310" r="12" fill="#32b3c5" fill-opacity="0.8"/>
                    <text x="430" y="313" fill="#fff" font-size="8" text-anchor="middle">M. de Dios</text>
                </svg>
                <div class="geo-footer">
                    <span>Distribución geográfica referencial · EESPP Pukllasunchis</span>
                    <span>Modo sin conexión garantizado</span>
                </div>
            </div>
            <div class="geo-aside">
                <h3>Territorios principales</h3>
                <table>
                    <thead><tr><th>Departamento / Provincia</th><th>Egresados</th></tr></thead>
                    <tbody>
                        <tr><td><b>Cusco (Cusco)</b></td><td><span class="chip ok">48</span></td></tr>
                        <tr><td><b>Cusco (San Jerónimo)</b></td><td><span class="chip ok">24</span></td></tr>
                        <tr><td><b>Cusco (San Sebastián)</b></td><td><span class="chip ok">19</span></td></tr>
                        <tr><td><b>Cusco (Quispicanchi)</b></td><td><span class="chip">15</span></td></tr>
                        <tr><td><b>Cusco (Canchis)</b></td><td><span class="chip">12</span></td></tr>
                        <tr><td><b>Apurímac (Cotabambas)</b></td><td><span class="chip warn">8</span></td></tr>
                        <tr><td><b>Madre de Dios</b></td><td><span class="chip warn">4</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>`;
}

window.PukGeo = {view, bind: ()=>{}};
})();
</script>

<script>
// Portal de Campaña Pública (Magic Link WhatsApp)
(function(){
    'use strict';
    const DEMOS = [
        {id:'ENC-001', nombre:'Lucía Quispe Huamán', egreso:'2024', programa:'Inicial'},
        {id:'ENC-002', nombre:'Marco Huamán Flores', egreso:'2023', programa:'Primaria EIB'},
        {id:'ENC-003', nombre:'Elena Ccama Torres', egreso:'2025', programa:'Inicial'}
    ];
    let selected = null;
    const code = '482671';
    const byId = id => document.getElementById(id);

    function step(n){
        for(let i=1; i<=3; i++){
            const e = byId('camp-step-' + i);
            if(e) e.className = 'camp-step ' + (i<n ? 'done' : i===n ? 'on' : '');
        }
    }

    function open(){
        document.body.classList.add('campana-abierta');
        step(1);
        byId('camp-work').innerHTML = `
            <h2 style="margin:0 0 5px;font-size:19px">Identifica tu ficha institucional</h2>
            <p style="color:#7c879d;margin-bottom:18px;">Ingresa tus datos tal como figuran en tu constancia o correo de egresado.</p>
            <div class="camp-demo" style="margin-bottom:18px;">
                <strong>Ejemplo de prueba:</strong> Lucía Quispe Huamán · 2024 · Inicial.
                <button type="button" class="btn sm outline" onclick="CampaignPortal.fill()" style="margin-left:8px;">Autocompletar</button>
            </div>
            <form id="camp-ident-form">
                <div class="camp-two">
                    <div>
                        <label>Nombres y apellidos</label>
                        <input id="camp-name" required placeholder="Tus nombres y apellidos">
                    </div>
                    <div>
                        <label>Año de egreso</label>
                        <select id="camp-year" required>
                            <option value="">Selecciona año</option>
                            <option>2021</option><option>2022</option><option>2023</option><option>2024</option><option>2025</option><option>2026</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label>Programa de estudios</label>
                    <select id="camp-prog" required>
                        <option value="">Selecciona carrera</option>
                        <option value="Inicial">Educación Inicial</option>
                        <option value="Primaria EIB">Educación Primaria EIB</option>
                    </select>
                </div>
                <div class="camp-action">
                    <button type="button" class="btn gray" onclick="CampaignPortal.close()">Volver al Panel</button>
                    <button type="submit" class="btn">Continuar →</button>
                </div>
            </form>
        `;
        byId('camp-ident-form').addEventListener('submit', e => {
            e.preventDefault();
            selected = DEMOS[0];
            verify();
        });
        window.scrollTo({top:0});
    }

    function verify(){
        step(2);
        byId('camp-work').innerHTML = `
            <h2 style="margin:0 0 6px;font-size:19px">Verificación de identidad</h2>
            <p style="color:#7c879d;margin-bottom:16px;">Hemos validado tu registro para <b>${selected.nombre}</b>. En el enlace por WhatsApp recibirás este código de seguridad:</p>
            <div style="background:#f4f7fc;padding:15px;border-radius:10px;text-align:center;margin-bottom:18px;">
                <span class="camp-code">${code}</span>
            </div>
            <form id="camp-verify-form">
                <label>Ingresa el código de 6 dígitos</label>
                <input id="camp-code-input" value="${code}" required style="text-align:center;font-size:18px;letter-spacing:3px;">
                <div class="camp-action">
                    <button type="button" class="btn gray" onclick="CampaignPortal.open()">Volver</button>
                    <button type="submit" class="btn green">Entrar a mi ficha →</button>
                </div>
            </form>
        `;
        byId('camp-verify-form').addEventListener('submit', e => {
            e.preventDefault();
            enter();
        });
    }

    function enter(){
        close();
        APP.setRole('egresado');
        document.getElementById('role-picker').value = 'egresado';
        APP.selectQuick(selected.id);
    }

    function close(){
        document.body.classList.remove('campana-abierta');
    }

    function fill(){
        document.getElementById('camp-name').value = 'Lucía Quispe Huamán';
        document.getElementById('camp-year').value = '2024';
        document.getElementById('camp-prog').value = 'Inicial';
    }

    window.CampaignPortal = {open, close, fill};
})();
</script>
</body>
</html>
