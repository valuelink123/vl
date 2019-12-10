<div class="row">
    <div class="col-md-12">
        <div class="portlet light bordered">

            <div class="portlet-body form">
                <form role="form" action="{{ url('ctg/store') }}" id="exception_form" method="POST">
                    {{ csrf_field() }}

                    <div class="form-body">

                        <div class="clearfix margin-bottom-20"></div>

                        <div class="form-group">
                            <label>Name</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-bookmark"></i>
                                </span>
                                <input type="text" class="form-control" name="name" id="name" value="{{old('name')}}" required >
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-bookmark"></i>
                                </span>
                                <input type="text" class="form-control" name="email" id="email" value="{{old('email')}}" required >
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Order ID</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-bookmark"></i>
                                </span>
                                <input type="text" class="form-control" name="order_id" id="order_id" value="{{old('order_id')}}" required pattern="\d{3}-\d{7}-\d{7}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Note</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-bookmark"></i>
                                </span>
                                <input type="text" class="form-control" name="note" id="note" value="{{old('note')}}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Channel</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-bookmark"></i>
                                </span>
                                <select class="form-control" name="channel" id="channel">
                                    @foreach ($channel as $key=>$value)
                                        <option value="{{$key}}" @if(old('channel')==$key) selected @endif>{{$value}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div style="clear:both;"></div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
                                <button type="submit" class="btn blue">Submit</button>
                                <button type="button"  class="btn grey-salsa btn-outline"  data-dismiss="modal" aria-hidden="true">Close</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

