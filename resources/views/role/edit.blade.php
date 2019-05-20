@extends('layouts.layout')
@section('label', 'Setting Rules')
@section('content')
    <h1 class="page-title font-red-intense"> Roles
    </h1>


    <div class="row"><div class="col-md-8">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-settings font-dark"></i>
                    <span class="caption-subject font-dark sbold uppercase">Roles Form</span>
                </div>
            </div>
            <div class="portlet-body form">
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                <form role="form" action="{{ url('role/'.$role['id']) }}" method="POST">
                    {{ csrf_field() }}
                    {{ method_field('PUT')}}
                    <div class="form-body">
                        <div class="form-group">
                            <label>Role Name</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-tag"></i>
                                </span>
                                <input type="text" class="form-control" name="name" id="name" value="{{$role['name']}}" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Display Name</label>
                            <div class="input-group col-md-3">
                                <span class="input-group-addon">
                                    <i class="fa fa-sort-amount-asc"></i>
                                </span>
                                <input type="text" class="form-control" name="display_name" id="display_name" value="{{$role['display_name']}}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-heart"></i>
                                </span>
                                <input type="text" class="form-control" name="description" id="description" value="{{$role['description']}}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Permissions</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
								<?php
								$permissions_group=[];
								foreach($permissions as $permission){
									if($permission['parent_id']){
										$permissions_group[$permission['parent_id']]['child'][]= ['id'=>$permission['id'],'display_name'=>$permission['display_name']];
									}else{
										$permissions_group[$permission['id']]['display_name']=$permission['display_name'];
									}
								}
								?>
                                <select name="permission[]" id="permission[]" class="mt-multiselect btn btn-default" multiple="multiple" data-clickable-groups="true" data-collapse-groups="true" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true">
									
									
                                    @foreach ($permissions_group as $key => $permission_group)
										 <optgroup label="{{$permission_group['display_name']}}">
										 		@foreach (array_get($permission_group,'child',[]) as $permission)
												<option value="{{$permission['id']}}" {{ in_array($permission['id'],$rolePermissions)?'selected':''}}>{{$permission['display_name']}}</option>
                                   				@endforeach
										 </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
                                <button type="submit" class="btn blue">Submit</button>
                                <button type="reset" class="btn grey-salsa btn-outline">Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
   
    </div>


@endsection
