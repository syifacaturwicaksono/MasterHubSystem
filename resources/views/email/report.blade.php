<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #333;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #999;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f2f2f2;
        }

        .yellow {
            background: yellow;
        }

        .orange {
            background: orange;
        }

        .section-title {
            margin-top: 20px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<p>
Menindaklanjuti perbaikan TAT yang lalu, maka untuk maintain TAT agar tetap baik, 
maka kita perlu cek progress sampai dengan finish repair / CLOSE CASE sbb:
</p>

<!-- PRIORITAS 1 -->
<p class="section-title">
1. Prioritas Pertama (TAT ≤ 5 Hari)
</p>

<table>
    <tr>
        <th>Case ID</th>
        <th>Received</th>
        <th>Start Repair</th>
        <th>Company</th>
        <th>Aging</th>
        <th>Status</th>
        <th>City</th>
    </tr>

    @foreach($fast as $row)
    <tr>
        <td>{{ $row->case_id }}</td>
        <td>{{ $row->received_date }}</td>
        <td>{{ $row->start_repair_date }}</td>
        <td>{{ $row->company_name }}</td>
        <td>{{ $row->tat }}</td>
        <td>{{ $row->case_status }}</td>
        <td>{{ $row->company_city }}</td>
    </tr>
    @endforeach
</table>


<!-- PRIORITAS 2 -->
<p class="section-title">
2. Prioritas Kedua (TAT > 5 Hari)
</p>

<table>
    <tr>
        <th>Case ID</th>
        <th>Received</th>
        <th>Start Repair</th>
        <th>Company</th>
        <th>Aging</th>
        <th>Status</th>
        <th>City</th>
    </tr>

    @foreach($slow as $row)
    <tr>
        <td>{{ $row->case_id }}</td>
        <td>{{ $row->received_date }}</td>
        <td>{{ $row->start_repair_date }}</td>
        <td>{{ $row->company_name }}</td>

        <!-- 🔥 WARNA -->
        <td class="{{ $row->tat > 10 ? 'orange' : 'yellow' }}">
            {{ $row->tat }}
        </td>

        <td class="{{ str_contains($row->case_status, 'Part Available') ? 'orange' : '' }}">
            {{ $row->case_status }}
        </td>

        <td>{{ $row->company_city }}</td>
    </tr>
    @endforeach
</table>

<br>

<p>
Semoga dengan cara ini, kita dapat meningkatkan performa TAT khususnya di Bench masing-masing.
</p>

<br>

<p>Regards,</p>

</body>
</html>