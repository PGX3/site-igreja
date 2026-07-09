<div class="noprint toolbar">
    <div class="grp">
        <span class="grp-label">Orientação</span>
        <div class="seg-row">
            <button type="button" class="seg" data-cfg="orient" data-val="portrait">Retrato</button>
            <button type="button" class="seg" data-cfg="orient" data-val="landscape">Paisagem</button>
        </div>
    </div>
    <div class="grp">
        <span class="grp-label">Colunas</span>
        <div class="seg-row">
            <button type="button" class="seg" data-cfg="cols" data-val="1">1</button>
            <button type="button" class="seg" data-cfg="cols" data-val="2">2</button>
        </div>
    </div>
    <div class="grp">
        <span class="grp-label">Fonte</span>
        <div class="seg-row">
            <button type="button" class="seg" data-fs-step="-1" title="Diminuir">A-</button>
            <button type="button" class="seg fs-reset" id="fs-valor" title="Restaurar tamanho padrão">16</button>
            <button type="button" class="seg" data-fs-step="1" title="Aumentar">A+</button>
        </div>
    </div>
    <div class="grp">
        <span class="grp-label">Espaçamento</span>
        <div class="seg-row">
            <button type="button" class="seg" data-cfg="lh" data-val="1.35">Compacto</button>
            <button type="button" class="seg" data-cfg="lh" data-val="1.7">Normal</button>
            <button type="button" class="seg" data-cfg="lh" data-val="2.1">Amplo</button>
        </div>
    </div>
    <div class="grp">
        <span class="grp-label">Margens</span>
        <div class="seg-row">
            <button type="button" class="seg" data-cfg="mar" data-val="normal">Normal</button>
            <button type="button" class="seg" data-cfg="mar" data-val="narrow">Estreita</button>
        </div>
    </div>
    <button type="button" class="btn-print" onclick="window.print()">🖨 Imprimir / Salvar PDF</button>
</div>

<style>
    .toolbar {
        display: flex; flex-wrap: wrap; gap: 18px; align-items: flex-end;
        padding: 14px 16px; border: 1px solid #e5e7eb; border-radius: 12px;
        background: #fafafa; font-family: Arial, Helvetica, sans-serif;
    }
    .toolbar .grp { display: flex; flex-direction: column; gap: 6px; }
    .toolbar .grp-label {
        font-size: 11px; font-weight: 700; letter-spacing: 0.06em;
        text-transform: uppercase; color: #888;
    }
    .toolbar .seg-row { display: flex; }
    .toolbar .seg {
        font: inherit; font-size: 13px; padding: 6px 14px; min-width: 40px;
        border: 1px solid #ccc; background: #fff; color: #333; cursor: pointer;
    }
    .toolbar .seg-row .seg:first-child { border-radius: 8px 0 0 8px; }
    .toolbar .seg-row .seg:last-child { border-radius: 0 8px 8px 0; }
    .toolbar .seg-row .seg + .seg { border-left: 0; }
    .toolbar .seg.active { background: #2563eb; border-color: #2563eb; color: #fff; }
    .toolbar .fs-reset { min-width: 46px; font-weight: 700; font-variant-numeric: tabular-nums; }
    .toolbar .btn-print {
        margin-left: auto; font: inherit; font-size: 14px; padding: 9px 18px;
        border: 0; border-radius: 10px; background: #111; color: #fff; cursor: pointer;
    }
</style>

<script>
(function () {
    var KEY = 'print-config';
    var FS_MIN = 10, FS_MAX = 30, FS_DEFAULT = 16;
    var defaults = { orient: 'portrait', cols: '1', fs: FS_DEFAULT, lh: '1.7', mar: 'normal' };
    var cfg = Object.assign({}, defaults);
    try { Object.assign(cfg, JSON.parse(localStorage.getItem(KEY) || '{}')); } catch (e) {}

    // Migra formatos antigos (fs guardado como 'sm'/'md'/'lg')
    if (typeof cfg.fs !== 'number') {
        cfg.fs = ({ sm: 14, md: 16, lg: 18 })[cfg.fs] || FS_DEFAULT;
    }
    cfg.fs = Math.min(FS_MAX, Math.max(FS_MIN, cfg.fs));
    if (!cfg.lh) cfg.lh = '1.7';

    var pageStyle = document.createElement('style');
    document.head.appendChild(pageStyle);
    var fsValor = document.getElementById('fs-valor');

    function save() {
        try { localStorage.setItem(KEY, JSON.stringify(cfg)); } catch (e) {}
    }

    function apply() {
        var b = document.body;
        b.classList.toggle('cols-2', cfg.cols === '2');
        b.classList.toggle('mar-narrow', cfg.mar === 'narrow');
        b.style.setProperty('--print-fs', cfg.fs + 'px');
        b.style.setProperty('--print-lh', cfg.lh);
        if (fsValor) fsValor.textContent = cfg.fs;
        var margin = cfg.mar === 'narrow' ? '10mm' : '18mm';
        pageStyle.textContent = '@page { size: A4 ' + cfg.orient + '; margin: ' + margin + '; }';
        document.querySelectorAll('.toolbar .seg[data-cfg]').forEach(function (btn) {
            btn.classList.toggle('active', String(cfg[btn.dataset.cfg]) === btn.dataset.val);
        });
    }

    // Botões de opção (orientação, colunas, espaçamento, margens)
    document.querySelectorAll('.toolbar .seg[data-cfg]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            cfg[btn.dataset.cfg] = btn.dataset.val;
            save();
            apply();
        });
    });

    // Stepper de fonte: quanto mais aperta, mais aumenta/diminui
    document.querySelectorAll('.toolbar .seg[data-fs-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            cfg.fs = Math.min(FS_MAX, Math.max(FS_MIN, cfg.fs + parseInt(btn.dataset.fsStep, 10)));
            save();
            apply();
        });
    });

    // Clique no valor central restaura o tamanho padrão
    if (fsValor) {
        fsValor.addEventListener('click', function () {
            cfg.fs = FS_DEFAULT;
            save();
            apply();
        });
    }

    apply();
})();
</script>
