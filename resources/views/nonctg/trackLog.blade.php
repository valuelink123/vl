<label>Track Log</label>
<table class="table table-striped table-bordered table-hover order-column">
    <thead>
    <tr>
        <th>Date</th>
        <th>email</th>
        <th>Content</th>
        <th>User</th>
    </tr>
    </thead>
    <tbody>
    @if($trackLogData)
    @foreach ($trackLogData as $data)
        <tr class="odd gradeX">
            <td>
                {{array_get($data,'created_at')}}
            </td>
            <td>
                {{array_get($data,'email')}}
            </td>
            <td>
                {!! $data['note'] !!}
            </td>
            <td>
                {{array_get($users,array_get($data,'processor'))}}
            </td>
        </tr>
    @endforeach
    @else
    <tr><td colspan="4" align='center'>No matching records found</td></tr>
    @endif

    </tbody>
</table>