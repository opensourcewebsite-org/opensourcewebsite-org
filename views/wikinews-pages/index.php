<?php
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = Yii::t('app', 'Wikinews');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-header d-flex p-0">
        <ul class="nav nav-pills ml-auto p-2">
            <li class="nav-item align-self-center mr-4">
				<button id="btn" class="btn btn-outline-success" data-toggle="modal" data-target="#addWikinewsModal" onclick = '$("#t_val").val("");'>
					Add Wikinews
				</button>
				<div id="addWikinewsModal" class="modal fade" role="dialog">
				  <div class="modal-dialog">
					<div class="modal-content">
					  <div class="modal-header">
						<h4 class="modal-title">Add Wikinews page</h4>
						<button type="button" class="close" data-dismiss="modal">&times;</button>
					  </div>
					  <div class="modal-body">
						<p>Wikinews page url</p>
						<input type="text" name="t_val" id="t_val" style ='width: 90%'>
					  </div>
					  <div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button id="btnSave" type="button" class="btn btn-success" data-dismiss="modal">Save</button>
					  </div>
					</div>

				  </div>
				</div>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'attribute' => 'title',
                    'contentOptions' => ['style' => 'width: 55%; white-space: normal'],				
                    'value' => function ($model) {
                        return Html::a(strlen($model['title']) > 150 ? substr($model["title"], 0, 150)."..." : $model["title"],$model['title'],['target'=>'_blank', 'data-pjax'=> 0]);
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'Language',
                    'contentOptions' => ['style' => 'width: 45%; white-space: normal'],				
                    'value' => function ($model) {
                        return $model['name'];
                    },
                    'format' => 'html',
                ],
				
            ],
			'pager' => [
                'options' => [
                    'class' => 'pagination float-right',
                ],
                'linkContainerOptions' => [
                    'class' => 'page-item',
                ],
                'linkOptions' => [
                    'class' => 'page-link',
                ],
                'maxButtonCount' => 5,
                'disabledListItemSubTagOptions' => [
                    'tag' => 'a',
                    'class' => 'page-link',
                ],
            ],			
        ]); ?>
    </div>	
</div>
<script type="text/javascript">
   window.onload = function(){
		$("#btnSave").click(function() {
			var title = $('[id*=t_val]').val();
            $.ajax({
                type: "POST",
                url: "wikinews-pages/add-link",
                data: {title : title},
                success: function (data) {
					alert (data);
                },
                error: function (data) {
                    alert('Error when inserting hte link into DB');
                }
            });	
		});
   }
</script>