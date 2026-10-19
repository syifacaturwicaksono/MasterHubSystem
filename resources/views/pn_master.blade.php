@extends('layouts.app')

@section('content')

<h3>🧩 PN Master</h3>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>No</th>
            <th>case_id</th>
            <th>PN Code</th>
            <th>Kategori</th>
            <th>part request</th>
            <th>SO no.</th>
            <th>ETA date</th>
            <th>part in date</th>

        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
        <tr>
            <td>{{ $row->id }}</td>
            <td>{{ $row->case_id }}</td>
            <td>{{ $row->pn_code }}</td>
            <td>{{ $row->kategori }}</td>
            <td>{{ $row->part_request }}</td>
            <td>{{ $row->so_no }}</td>
            <td>{{ $row->eta_date }}</td>
            <td>{{ $row->partin_date }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection