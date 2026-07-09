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
            <button type="button" class="seg" data-cfg="fs" data-val="sm">A-</button>
            <button type="button" class="seg" data-cfg="fs" data-val="md">A</button>
            <button type="button" class="seg" data-cfg="fs" data-val="lg">A+</button>
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
    .toolbar .btn-print {
        margin-left: auto; font: inherit; font-size: 14px; padding: 9px 18px;
        border: 0; border-radius: 10px; background: #111; color: #fff; cursor: pointer;
    }
</style>

<script>
(function () {
    var KEY = 'print-config';
    var defaults = { orient: 'portrait', cols: '1', fs: 'md', mar: 'normal' };
    var cfg = Object.assign({}, defaults);
    try { Object.assign(cfg, JSON.parse(localStorage.getItem(KEY) || '{}')); } catch (e) {}

    var pageStyle = document.createElement('style');
    document.head.appendChild(pageStyle);

    function apply() {
        var b = document.body;
        b.classList.toggle('cols-2', cfg.cols === '2');
        b.classList.remove('fs-sm', 'fs-md', 'fs-lg');
        b.classList.add('fs-' + cfg.fs);
        b.classList.toggle('mar-narrow', cfg.mar === 'narrow');
        var margin = cfg.mar === 'narrow' ? '10mm' : '18mm';
        pageStyle.textContent = '@page { size: A4 ' + cfg.orient + '; margin: ' + margin + '; }';
        document.querySelectorAll('.toolbar .seg').forEach(function (btn) {
            btn.classList.toggle('active', cfg[btn.dataset.cfg] === btn.dataset.val);
        });
    }

    document.querySelectorAll('.toolbar .seg').forEach(function (btn) {
        btn.addEventListener('click', function () {
            cfg[btn.dataset.cfg] = btn.dataset.val;
            try { localStorage.setItem(KEY, JSON.stringify(cfg)); } catch (e) {}
            apply();
        });
    });

    apply();
})();
</script>
