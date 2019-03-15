

    <div class="row"><div class="col-md-12">
        <div class="portlet light bordered">

            <div class="portlet-body form">
                <form role="form" action="{{ url('category') }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-body">
                        <div class="form-group col-md-12" style="margin-top: 40px;">
                            <label>Superior category</label>

                            <select name="superior_category" class="form-control " required>
                                <option value="0">Primary category</option>
                                <?php
                                echo procHtml($tree);
//                                foreach($lists as $key=>$val){
//                                    if($t['cate_ParentId'] == ''){
//                                        echo '<option value="'.$val['category_id'].'">'.$val['category_name'].' </option>';
//                                        $html .= "<li>{$t['cate_Name']}</li>";
//                                    }else{
//                                        $html .= "<li>".$t['cate_Name'];
//                                        $html .= procHtml($t['cate_ParentId']);
//                                        $html = $html."</li>";
//                                    }
//
//                                }
                                ?>
                            </select>

                        </div>

						<div class="form-group col-md-12">
                            <label>Category name</label>


                                <input type="text" class="form-control" name="category_name" id="category_name" value="" required>

                        </div>




                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
								<button type="button"  class="btn grey-salsa btn-outline pull-right"  data-dismiss="modal" aria-hidden="true">Close</button>
                                <button type="submit" class="btn blue pull-right">Submit</button>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>
    <?php
    function procHtml($tree,$level = 0)
    {
        $html = '';
        foreach($tree as $key=>$val)
        {
            if($val['category_pid'] == '') {
                $html .= '<option value="'.$val['id'].'">'.$val['category_name'].' </option>';
            }else{
                $flg = str_repeat('|----',$level);
                $html .= '<option value="'.$val['id'].'">'.$flg.$val['category_name'];
                $html .= procHtml($val['category_pid'],$level+1);
                $html = $html."</option>";
            }
        }
        return $html;
    }
    ?>