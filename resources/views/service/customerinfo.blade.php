<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>

<div class="portlet light bordered">

    <div class="portlet-body">
        <div>
            <ul class="nav nav-tabs" role="tablist" id="tabs">
                <li role="presentation" class="active"><a href="#ctg-info" aria-controls="ctg-info" role="tab" data-toggle="tab">Contact Info</a></li>
                <li role="presentation"><a href="#order-info" aria-controls="order-info" role="tab" data-toggle="tab">Amazon Order Info</a></li>
                <li role="presentation"><a href="#email-history" aria-controls="email-history" role="tab" data-toggle="tab">Email History</a></li>
                <li role="presentation"><a href="#rsg-request" aria-controls="email-history" role="tab" data-toggle="tab">Rsg Request List</a></li>
            </ul>

            <div class="tab-content">

                <div role="tabpanel" class="tab-pane active" id="ctg-info">
                    <form class="row">
                        <div class="col-md-8">
                            @if($contactInfo)
                                @foreach ($contactInfo as $data)
                                    @if($data['name'])Name: {!! $data['name'] !!}<br/>@endif
                                    @if($data['email'])Email: {!! $data['email'] !!}<br/>@endif
                                    @if($data['phone'])Phone: {!! $data['phone'] !!}<br/>@endif
                                    @if($data['country'])Country: {!! $data['country'] !!}<br/>@endif
                                    @if($data['remark'])Remark: {!! $data['remark'] !!}<br/>@endif
                                    <hr>
                                @endforeach
                            @else
                                <b>Contact not found.</b>
                            @endif
                            <br/>
                        </div>
                    </form>
                </div>

                <div role="tabpanel" class="tab-pane" id="order-info">
                    @include('nonctg.orderInfo')
                </div>

                <div role="tabpanel" class="tab-pane" id="email-history">
                    <div class="table-container">
                        @include('nonctg.emailList')
                    </div>
                    <div class="table-container">
                        @include('crm.trackLogList')
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="rsg-request">
                    <div class="table-container">
                        @include('crm.rsgRequestList')
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>