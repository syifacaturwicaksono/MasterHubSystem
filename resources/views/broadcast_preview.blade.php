<div style="border:1px solid #dee2e6; border-radius:12px; overflow:hidden;">

    <div style="
        background:#f8f9fa;
        padding:16px 20px;
        border-bottom:1px solid #dee2e6;
        position:sticky;
        top:0;
        z-index:2;
    ">
        <strong>Subject :</strong> {{ $subject }}
    </div>

    <div style="
        padding:22px;
        font-family:'Segoe UI', Arial, sans-serif;
        font-size:13px;
        line-height:1.6;
        color:#111;
        max-height:500px;
        overflow-y:auto;
        overflow-x:hidden;
    ">
        {!! $body !!}
    </div>

</div>