<div class="row">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet light bordered">
            <div class="portlet-body">
                    <form id="whole_form"  name="whole_form" >
                        {{ csrf_field() }}
                        <div class="form-body">
                            <h2 class="page-title font-red-intense">Campaign</h2>
                            <input type="hidden" name="profile_id" value="{{$profile_id}}">
                            <input type="hidden" name="ad_type" value="{{$ad_type}}">
                            <div class="row">
                            <div class="col-md-6">
                            <div class="form-group">
                                <label>Campaign Name:</label>
                                <input type="text" class="form-control" name="CampaignName" id="CampaignName" required >
                            </div>
                            
                            <div class="form-group date date-picker" data-date-format="yyyy-mm-dd" >
                                <label>Date Range:</label>
                                <div class="row" style="margin-top:0px;">
                                <div class="col-md-6">
                                <div class="input-group date date-picker " data-date-format="yyyy-mm-dd">
                                    <input type="text" class="form-control" readonly name="startDate" id="startDate" value="{{date('Y-m-d')}}" required>
                                    <span class="input-group-btn">
                                        <button class="btn default" type="button">
                                            <i class="fa fa-calendar"></i>
                                        </button>
                                    </span>
                                </div>
                                </div>
                                <div class="col-md-6">
                                <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                    <input type="text" class="form-control" readonly name="endDate" id="endDate" value="">
                                    <span class="input-group-btn">
                                        <button class="btn default" type="button">
                                            <i class="fa fa-calendar"></i>
                                        </button>
                                    </span>
                                </div></div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                            <label>Targeting Type:</label>
                            <select class="form-control" name="targetingType" id="targetingType">
                                <option value="auto">Auto</option>
                                <option value="manual">Manual</option>
                            </select>
                            </div>

                            <div class="form-group">
                            <label>Targeting:</label>
                            <select class="form-control" name="target" id="target" disabled>
                                <option value="targets">Products</option>
                                <option value="keywords">Keywords</option>
                            </select>
                            </div>
                            </div>
                            <div class="col-md-6">
                            <div class="form-group">
                                <label>Budget:</label>
                                <input type="hidden" name="campaignType" value="sponsoredProducts">
                                <input type="text" class="form-control" name="dailyBudget" id="dailyBudget" value="0">  
                            </div>

                            <div class="form-group">
                                <label>Bidding Strategy:</label>
                                <select class="form-control" name="strategy" id="strategy">
                                <?php 
                                foreach(\App\Models\PpcProfile::BIDDING as $k=>$v){ 	
                                    echo '<option value="'.$k.'">'.$v.'</option>';
                                }?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="">Adjust bids by placement - Top of search (first page):</label>
                                <div class="input-group">
                                <input type="text" class="form-control" name="placementTop" id="placementTop" value="0">
                                <span class="input-group-btn">
                                <button class="btn default" type="button">%</button>
                                </span>
                                </div>
                            </div>
                            <div class="form-group">   
                                <label class="">Adjust bids by placement - Product pages:</label>
                                <div class="input-group">
                                <input type="text" class="form-control" name="placementProductPage" id="placementProductPage" value="0">
                                <span class="input-group-btn">
                                <button class="btn default" type="button">%</button>
                                </span>
                                </div>
                            </div>
                            </div></div>

                            <h2 class="page-title font-red-intense">Ad Group</h2>

                            <div class="row">
                            <div class="col-md-6">
                            <div class="form-group">
                                <label>AdGroup Name:</label>
                                <input type="text" class="form-control" name="adGroupName" id="adGroupName" required>
                            </div>
                            </div>
                            <div class="col-md-6">
                            <div class="form-group">
                                <label>Default Bid:</label>
                                <input type="text" class="form-control" name="defaultBid" id="defaultBid" value="0">  
                            </div>
                            </div></div>

                            <h2 class="page-title font-red-intense">Ad</h2>

                            <div class="row">
                            <div class="col-md-12">
                            <div class="form-group">
                                <label>Ads:</label>
                                <select class="mt-multiselect form-control " multiple="multiple" name="ads[]" id="ads" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true">
							<?php 
							foreach($products as $v){ 	
								echo '<option value="'.$v->seller_sku.'">'.$v->seller_sku.' - '.$v->asin.'</option>';
							}?>
							</select>
                            </div>
                            </div>
                            </div>


                            <div id="campaign_keywords" style="display:none;">

                            <h2 class="page-title font-red-intense">Keywords</h2>

                            <div class="row">
                            <div class="col-md-6">
                            <div class="form-group">
                                <label>Bid Option:</label>
                                <select class="form-control" name="KeywordBidOption" id="KeywordBidOption">
                                <option value="suggested" >Suggested Bid</option>
                                <option value="customize" >Default Bid</option>
                                </select>
                            </div>
                            </div>
                            <div class="col-md-6">
                            <div class="form-group">
                                <label>Default Bid:</label>
                                <input type="text" class="form-control" name="keywordDefaultBid" id="keywordDefaultBid" value="0">  
                            </div>
                            </div>
                            <div class="col-md-4">
                            <div class="form-group">
                                <label>Broad Keywords:</label>
                                <textarea class="form-control" rows="10" name="Broad" id="Broad"
                                placeholder="Enter your list and separate each item whith a new line."></textarea>
                            </div>
                            </div>
                            <div class="col-md-4">
                            <div class="form-group">
                                <label>Phrase Keywords:</label>
                                <textarea class="form-control" rows="10" name="Phrase" id="Phrase"
                                placeholder="Enter your list and separate each item whith a new line."></textarea>
                            </div>
                            </div>
                            <div class="col-md-4">
                            <div class="form-group">
                                <label>Exact Keywords:</label>
                                <textarea class="form-control" rows="10" name="Exact" id="Exact"
                                placeholder="Enter your list and separate each item whith a new line."></textarea>
                            </div>
                            </div>
                        </div>
                        </div>
                        <div id="campaign_targets" style="display:none;">
                            <h2 class="page-title font-red-intense">Targets</h2>

                            <div class="row">
                            <div class="col-md-6">
                            <div class="form-group">
                                <label>Bid Option:</label>
                                <select class="form-control" name="targetBidOption" id="targetBidOption">
                                <option value="suggested" >Suggested Bid</option>
                                <option value="customize" >Default Bid</option>
                                </select>
                            </div>
                            </div>
                            <div class="col-md-6">
                            <div class="form-group">
                                <label>Default Bid:</label>
                                <input type="text" class="form-control" name="targetDefaultBid" id="targetDefaultBid" value="0">  
                            </div>
                            </div>
                            <div class="col-md-4">
                            <div class="form-group">
                                <label>Asin same as:</label>
                                <textarea class="form-control" rows="10" name="asinSameAs" id="asinSameAs"
                                placeholder="Enter your list and separate each item whith a new line."></textarea>
                            </div>
                            </div>
                            <div class="col-md-4">
                            <div class="form-group">
                                <label>Catetory same as:</label>
                                <textarea class="form-control" rows="10" name="asinCategorySameAs" id="asinCategorySameAs"
                                placeholder="Enter your list and separate each item whith a new line."></textarea>
                            </div>
                            </div>
                            <div class="col-md-4">
                            <div class="form-group">
                                <label>Brand same as:</label>
                                <textarea class="form-control" rows="10" name="asinBrandSameAs" id="asinBrandSameAs"
                                placeholder="Enter your list and separate each item whith a new line."></textarea>
                            </div>
                            </div>
                        </div></div>

                            <div style="clear:both;"></div>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="form-actions col-md-12">
                            <div class="row">
                                <div class="col-md-12">
                                <button type="button"  class="btn grey-salsa btn-outline pull-right"  data-dismiss="modal" aria-hidden="true">Close</button>
                                    <input type="submit" name="update" value="Save" class="btn blue pull-right" >
                                    
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                        <div style="clear:both;"></div>
                    </form>
            </div>
        </div>
    </div>
</div>

<script>

$(function() {
    $('.date-picker').datepicker({
        rtl: App.isRTL(),
        autoclose: true
    });

    $('#targetingType,#target').on('change',function(){
        $('#campaign_targets').hide();
        $('#campaign_keywords').hide();
        if($('#targetingType').val()=='auto'){
            $('#target').attr("disabled",true);
        }else{
            $('#target').attr("disabled",false);
            $('#campaign_'+$('#target').val()).show();
        }
    });

    $('#whole_form').submit(function() {
		$.ajaxSetup({
			headers: { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' }
		});
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "{{ url('/adv/saveWhole') }}",
			data: $('#whole_form').serialize(),
			success: function (data) {
				if(data.code=='SUCCESS'){
                    $('#ajax').modal('hide');
					$('.modal-backdrop').remove();
                    toastr.success(data.description);
                    var dttable = $('#datatable_ajax').dataTable();
					dttable.api().ajax.reload(null, false);
				}else{
					toastr.error(data.description);
				}
			},
			error: function(data) {
                toastr.error(data.responseText);
			}
		});
		return false;
	});
});
var MultiselectInit=function(){return{init:function(){$("#whole_form .mt-multiselect").each(function(){var t,a=$(this).attr("class"),i=$(this).data("clickable-groups")?$(this).data("clickable-groups"):!1,l=$(this).data("collapse-groups")?$(this).data("collapse-groups"):!1,o=$(this).data("drop-right")?$(this).data("drop-right"):!1,e=($(this).data("drop-up")?$(this).data("drop-up"):!1,$(this).data("select-all")?$(this).data("select-all"):!1),s=$(this).data("width")?$(this).data("width"):"",n=$(this).data("height")?$(this).data("height"):"",d=$(this).data("filter")?$(this).data("filter"):!1,h=function(t,a,i){},r=function(t){alert("Dropdown shown.")},c=function(t){alert("Dropdown Hidden.")},p=1==$(this).data("action-onchange")?h:"",u=1==$(this).data("action-dropdownshow")?r:"",b=1==$(this).data("action-dropdownhide")?c:"";t=$(this).attr("multiple")?'<li class="mt-checkbox-list"><a href="javascript:void(0);"><label class="mt-checkbox"> <span></span></label></a></li>':'<li><a href="javascript:void(0);"><label></label></a></li>',$(this).multiselect({enableClickableOptGroups:i,enableCollapsibleOptGroups:l,disableIfEmpty:!0,enableFiltering:d,includeSelectAllOption:e,dropRight:o,buttonWidth:s,maxHeight:n,onChange:p,onDropdownShow:u,onDropdownHide:b,buttonClass:a})})}}}();jQuery(document).ready(function(){MultiselectInit.init()});
</script>

