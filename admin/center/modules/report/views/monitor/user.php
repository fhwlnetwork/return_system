<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use center\extend\Tool;
\center\assets\ReportAsset::echartsJs($this);
use center\modules\report\models\Efficiency;
$this->title = Yii::t('app', 'report/monitor/user');
 ?>
<div class="page page-dashboard">
	<div class="panel panel-default">
		<br />
		<div class="page page-dashboard">
			<div class="row">
				  <div class="col-md-12 text-left">
					  <h3><?= Yii::t('app', 'user auth inspect');?></h3>
					  <div class="h_b"></div>
					  <!-- 表单 start -->
					  <div style="height:22px;margin-top:5px;">					
						<?php $form = ActiveForm::begin([
							'id' => 'chgPass',
							'layout' => 'horizontal',
							'fieldConfig' => [
								'horizontalCssClasses' => [
									'label' => 'col-sm-2',
									'offset' => 'col-sm-offset-1',
									'wrapper' => 'col-sm-4',
									'error' => '',
									'hint' => '',
								],
							],
						]); ?>													
							<div style="width:40%;float:left;">
							<input type="text" class="form-control" name="username" value="<?= $user['user_name'];?>" placeholder="<?= Yii::t('app', 'account');?>">
							</div>
							<div style="width:10px;float:left;">　</div>
							 <div style="width:5%;float:left;">
							<button type="submit" class="btn btn-success"><?= Yii::t('app', 'inspect');?></button>
							</div>
						<?php $form->end()?>

						  <div style="width:5%;float:left;">
							  <?= Html::button(Yii::t('app', 'debug'), [
									  'class' => 'btn btn-success',
									  'onclick' => 'debug()',
							  ])?>
						  </div>
                          <?php if(Yii::$app->user->can('report/monitor/clear')):?>
						  <div style="width:5%;float:left;">
							  <?php echo Html::a(Html::button(Yii::t('app', 'clear'), ['class'=>'btn btn-success']), ['clear', 'username' => $user['user_name']], [
									  'title' => Yii::t('app', 'clear'),
									  'data' => [
											  'method' => 'post',
											  'confirm' => Yii::t('app', 'report user help'),
									  ],
							  ]) ?>
						  </div>
                          <?php endif?>
						  <!--提示信息显示-->
						  <div style="width:20%;float:left;padding-top:5px;">
							  <span id="error_msg" style="size:14px;"></span>
						  </div>
					  </div>
					  <!-- 表单 end -->
					  <br />
					  <br />
						<div class="row">
							  <?php
								if($user){
							 ?>
							  <div class="col-md-12 text-center">
								  <div class="text-left">
									<div class="text-left" style="border-bottom:dashed 1px #ccc;">
										<h4><?= Yii::t('app', 'basic info');?></h4>
									</div>
									<div class="col-md-2">
										<h5><?= Yii::t('app', 'account');?>：<?= $user['user_name'];?></h5>
									</div>
									<div class="col-md-2">
										<h5><?= Yii::t('app', 'user real name');?>：<?= $user['user_real_name'];?></h5>
									</div>
									<div class="col-md-2">
										<h5><?= Yii::t('app', 'email');?>：<?= $user['email'];?></h5>
									</div>
									<div class="col-md-3">
										<h5><?= Yii::t('app', 'created_at');?>：<?= date('Y-m-d H:i:s',$user['user_create_time']);?></h5>
									</div>									  
								  </div>	  
							  </div>	
							  <?php
								}
							   ?> 	
							  <?php
								if(!empty($product)){
							 ?>
							  <div class="col-md-12 text-left">
									<div style="height:10px;"></div>
									<div class="text-left" style="border-bottom:dashed 1px #ccc;">
										<h4><?= Yii::t('app', 'subscribe product');?></h4>
									</div>
									<div class="col-md-5">
									<table cellpadding="0" cellspacing="0" border="0" width="100%">
										<?php
										foreach($product as $key=>$value){
										?>
										<tr>
											<td><h5><?= $value['products_name'];?></h5></td>
											<td width="40%" align="right"><h5><?= $value['checkout_amount'];?>/余额</h5></td>
										</tr>
										<?php
										}
										?>									
									</table>
									</div>  
							  </div>	
							  <?php
								}
							   ?>
							<?php
							if(!empty($login_log)){
								?>
								<div class="col-md-12 text-left">
									<div style="height:10px;"></div>
									<div class="text-left" style="border-bottom:dashed 1px #ccc;">
										<h4><?= Yii::t('app', 'auth info');?></h4>
									</div>
									<div class="col-md-12">
										<table cellpadding="0" cellspacing="0" border="0" width="100%">
											<thead>
											<tr style="padding-top:5px;">
												<td width="10%"><h5><?= Yii::t('app', 'account');?></h5></td>
												<td width="10%"><h5>IP</h5></td>
												<td width="15%"><h5>NAS IP</h5></td>
												<td width="15%"><h5><?= Yii::t('app', 'mac');?></h5></td>
												<td width="15%"><h5><?= Yii::t('app', 'port');?></h5></td>
												<td width="15%"><h5><?= Yii::t('app', 'error msg');?></h5></td>
												<td width="15%"><h5><?= Yii::t('app', 'log time');?></h5></td>
											</tr>
											</thead>
											<tbody>
											<tr>
												<td width="10%"><?=$login_log['user_name']?></td>
												<td width="10%"><?=$login_log['user_ip']?></td>
												<td width="15%"><?=$login_log['nas_ip']?></td>
												<td width="15%"><?=$login_log['user_mac']?></td>
												<td width="15%"><?=$login_log['nas_port_id']?></td>
												<td width="15%"><?=$login_log['err_msg']?></td>
												<td width="15%"><?=$login_log['log_time']!=''?date('Y-m-d H:i:s',$login_log['log_time']):''?></td>
											</tr>
											</tbody>
										</table>
									</div>
								</div>
								<?php
							}
							?>
						</div>						  
				  </div>
				  </div>
				  <br />
				  <div class="row">
					<div class="col-md-12 text-center">
						<div class="text-left" style="border-bottom:dashed 1px #ccc;">
							<h4><?= Yii::t('app', 'inspect info');?></h4>
						</div>
					</div>		
				  <div class="col-md-12">			  
						<h5><?= Yii::t('app', 'auth');?></h5>
						<div class="col-md-12">
							<div class="col-lg-2 text-center  percent12">										
								<div class="panel mini-boxsye <?= $portal_auth['bgcolor'];?>">
								Srun_portal_server<br />
								<span><?= !empty($portal_auth['ip'])?$portal_auth['ip'].' / ':'';?><?= !empty($portal_auth['time'])?sprintf("%.2f",$portal_auth['time']):'';?></span>
								</div>
							</div>														
							<div class="col-lg-1 text-center percent5">
								<h1><i class="fa fa-arrow-right"></i></h1>
							</div>
							<div class="col-lg-2 text-center  percent12">
								<div class="panel mini-boxsye <?= $Radiusd_auth['bgcolor'];?>">
								Radiusd<br />
								<span><?= !empty($Radiusd_auth['ip'])?$Radiusd_auth['ip'].' / ':'';?><?= !empty($Radiusd_auth['time'])?sprintf("%.2f",$Radiusd_auth['time']):'';?></span>							
								</div>
							</div>	
							<div class="col-lg-1 text-center percent5">
								<h1><i class="fa fa-arrow-right"></i></h1>
							</div>
							<div class="col-lg-2 text-center  percent12">
								<div class="panel mini-boxsye <?= $third_auth['bgcolor'];?>">
								Third_auth<br />
								<span><?= !empty($third_auth['ip'])?$third_auth['ip'].' / ':'';?><?= !empty($third_auth['time'])?sprintf("%.2f",$third_auth['time']):'';?></span>							
								</div>
							</div>														
							<div class="col-lg-2 text-center  percent12">
								<div class="panel mini-boxsye <?= $proxy_3p['bgcolor'];?>">
								Proxy_3p<br />
								<span><?= !empty($proxy_3p['ip'])?$proxy_3p['ip'].' / ':'';?><?= !empty($proxy_3p['time'])?sprintf("%.2f",$proxy_3p['time']):'';?></span>								
								</div>
							</div>							
						</div>
						<h5><?= Yii::t('app', 'billing start');?></h5>
						<div class="col-md-12">
							<div class="col-lg-2 text-center  percent12">
								<div class="panel mini-boxsye <?= $Radiusd_start['bgcolor'];?>">
								Radiusd<br />
								<span><?= !empty($Radiusd_start['ip'])?$Radiusd_start['ip'].' / ':'';?><?= !empty($Radiusd_start['time'])?sprintf("%.2f",$Radiusd_start['time']):'';?></span>							
								</div>
							</div>
							<div class="col-lg-1 col-xsm-6 text-center percent5">
								<h1><i class="fa fa-arrow-right"></i></h1>
							</div>
							<div class="col-lg-2 text-center  percent12">
								<div class="panel mini-boxsye <?= $Rad_auth_start['bgcolor'];?>">
								Rad_auth<br />
								<span><?= !empty($Rad_auth_start['ip'])?$Rad_auth_start['ip'].' / ':'';?><?= !empty($Rad_auth_start['time'])?sprintf("%.2f",$Rad_auth_start['time']):'';?></span>									
								</div>
							</div>	
							<div class="col-lg-1 col-xsm-6 text-center percent5">
								<h1><i class="fa fa-arrow-right"></i></h1>
							</div>
							<div class="col-lg-2 text-center  percent12">
								<div class="panel mini-boxsye <?= $Distribute_start['bgcolor'];?>">
								Distribute<br />
								<span><?= !empty($Distribute_start['ip'])?$Distribute_start['ip'].' / ':'';?><?= !empty($Distribute_start['time'])?sprintf("%.2f",$Distribute_start['time']):'';?></span>								
								</div>
							</div>	
							<div class="col-lg-2 text-center  percent12">
								<div class="panel mini-boxsye <?= $wangkang_3p['bgcolor'];?>">
								wangkang_3p<br />
								<span><?= !empty($wangkang_3p['ip'])?$wangkang_3p['ip'].' / ':'';?><?= !empty($wangkang_3p['time'])?sprintf("%.2f",$wangkang_3p['time']):'';?>
								</div>
							</div>	
							<div class="col-lg-2 text-center  percent12">
								<div class="panel mini-boxsye <?= $stoneos_3p['bgcolor'];?>">
								stoneos_3p<br />
								<span><?= !empty($stoneos_3p['ip'])?$stoneos_3p['ip'].' / ':'';?><?= !empty($stoneos_3p['time'])?sprintf("%.2f",$stoneos_3p['time']):'';?>
								</div>
							</div>								
						</div>
						<div class="col-md-12">
							<div class="col-lg-2 text-center  percent12"></div>
							<div class="col-lg-1 col-xsm-6 text-center percent5"></div>
							<div class="col-lg-2 text-center  percent12"></div>	
							<div class="col-lg-1 col-xsm-6 text-center percent5"></div>
							<div class="col-lg-2 text-center  percent12">
								<div class="panel mini-boxsye <?= $online2db['bgcolor'];?>">
								Online2db<br />
								<span><?= !empty($online2db['ip'])?$online2db['ip'].' / ':'';?><?= !empty($online2db['time'])?sprintf("%.2f",$online2db['time']):'';?></span>									
								</div>
							</div>	
							<div class="col-lg-2 text-center  percent12">
								<div class="panel mini-boxsye <?= $allot_3p['bgcolor'];?>">
								allot 3p<br />
								<span><?= !empty($allot_3p['ip'])?$allot_3p['ip'].' / ':'';?><?= !empty($allot_3p['time'])?sprintf("%.2f",$allot_3p['time']):'';?>
								</div>
							</div>	
							<div class="col-lg-2 text-center  percent12">
								<div class="panel mini-boxsye <?= $shenxunfu_3p['bgcolor'];?>">
								shenxunfu_3p<br />
								<span><?= !empty($shenxunfu_3p['ip'])?$shenxunfu_3p['ip'].' / ':'';?><?= !empty($shenxunfu_3p['time'])?sprintf("%.2f",$shenxunfu_3p['time']):'';?>
								</div>
							</div>								
						</div>		
						<h5><?= Yii::t('app', 'billing update');?></h5>
						<div class="col-md-12">
							<div class="col-lg-2 text-center percent12">										
								<div class="panel mini-boxsye <?= $Radiusd_update['bgcolor'];?>">
								Radiusd<br />
								<span><?= !empty($Radiusd_update['ip'])?$Radiusd_update['ip'].' / ':'';?><?= !empty($Radiusd_update['time'])?sprintf("%.2f",$Radiusd_update['time']):'';?>
								</div>
							</div>														
							<div class="col-lg-1 text-center percent5">
								<h1><i class="fa fa-arrow-right"></i></h1>
							</div>
							<div class="col-lg-2 text-center percent12">
								<div class="panel mini-boxsye <?= $Rad_auth_update['bgcolor'];?>">
								Rad_auth<br />
								<span><?= !empty($Rad_auth_update['ip'])?$Rad_auth_update['ip'].' / ':'';?><?= !empty($Rad_auth_update['time'])?sprintf("%.2f",$Rad_auth_update['time']):'';?>
								</div>
							</div>	
							<div class="col-lg-1 text-center percent5"></div>
							<div class="col-lg-2 text-center"></div>										
							<div class="col-lg-2 text-center"></div>							
						</div>		
						<h5><?= Yii::t('app', 'billing end');?></h5>
						<div class="col-md-12">
							<div class="col-lg-2 text-center percent12" style="height:90px;">
								<div class="firsthalf <?= $portal_stop['bgcolor'];?>">
								Srun_portal_server<br />
								<span style="font-size:12px;"><?= !empty($portal_stop['ip'])?$portal_stop['ip'].' / ':'';?><?= !empty($portal_stop['time'])?sprintf("%.2f",$portal_stop['time']):'';?>
								</span></div>
								<div class="bottom_half <?= $rad_dm['bgcolor'];?>">
								Rad_dm<br />
								<span style="font-size:12px;"><?= !empty($rad_dm['ip'])?$rad_dm['ip'].' / ':'';?><?= !empty($rad_dm['time'])?sprintf("%.2f",$rad_dm['time']):'';?></span>
								</div>	
							</div>
							<div class="col-lg-1 col-xsm-6 text-center percent5">
								<h1><i class="fa fa-arrow-right"></i></h1>
							</div>
							<div class="col-lg-2 text-center percent12">
								<div class="panel mini-boxsye <?= $Radiusd_stop['bgcolor'];?>">
								Radiusd<br />
								<span><?= !empty($Radiusd_stop['ip'])?$Radiusd_stop['ip'].' / ':'';?><?= !empty($Radiusd_stop['time'])?sprintf("%.2f",$Radiusd_stop['time']):'';?>
								</div>
							</div>
							<div class="col-lg-1 col-xsm-6 text-center percent5">
								<h1><i class="fa fa-arrow-right"></i></h1>
							</div>
							<div class="col-lg-2 text-center percent12">
								<div class="panel mini-boxsye <?= $Rad_auth_stop['bgcolor'];?>">
								Rad_auth<br />
								<span><?= !empty($Rad_auth_stop['ip'])?$Rad_auth_stop['ip'].' / ':'';?><?= !empty($Rad_auth_stop['time'])?sprintf("%.2f",$Rad_auth_stop['time']):'';?>
								</div>
							</div>	
							<div class="col-lg-1 col-xsm-6 text-center percent5">
								<h1><i class="fa fa-arrow-right"></i></h1>
							</div>
							<div class="col-lg-2 text-center percent12">
								<div class="panel mini-boxsye <?= $Distribute_stop['bgcolor'];?>">
								Distribute<br />
								<span><?= !empty($Distribute_stop['ip'])?$Distribute_stop['ip'].' / ':'';?><?= !empty($Distribute_stop['time'])?sprintf("%.2f",$Distribute_stop['time']):'';?>
								</div>
							</div>	
							<div class="col-lg-2 text-center percent12">
								<div class="panel mini-boxsye <?= $wangkang_3p_s['bgcolor'];?>">
								wangkang_3p<br />
								<span><?= !empty($wangkang_3p_s['ip'])?$wangkang_3p_s['ip'].' / ':'';?><?= !empty($wangkang_3p_s['time'])?sprintf("%.2f",$wangkang_3p_s['time']):'';?>
								</div>
							</div>	
							<div class="col-lg-2 text-center percent12">
								<div class="panel mini-boxsye <?= $stoneos_3p_s['bgcolor'];?>">
								stoneos_3p<br />
								<span><?= !empty($stoneos_3p_s['ip'])?$stoneos_3p_s['ip'].' / ':'';?><?= !empty($stoneos_3p_s['time'])?sprintf("%.2f",$stoneos_3p_s['time']):'';?>
								</div>
							</div>								
						</div>
						<div class="col-md-12">
							<div class="col-lg-2 text-center percent12"></div>
							<div class="col-lg-1 col-xsm-6 text-center percent5"></div>
							<div class="col-lg-2 text-center percent12"></div>
							<div class="col-lg-1 col-xsm-6 text-center percent5"></div>
							<div class="col-lg-2 text-center percent12"></div>
							<div class="col-lg-1 col-xsm-6 text-center percent5"></div>							
							<div class="col-lg-2 text-center percent12">
								<div class="panel mini-boxsye <?= $Online2db_s['bgcolor'];?>">
								Online2db<br />
								<span><?= !empty($Online2db_s['ip'])?$Online2db_s['ip'].' / ':'';?><?= !empty($Online2db_s['time'])?sprintf("%.2f",$Online2db_s['time']):'';?>
								</div>
							</div>	
							<div class="col-lg-2 text-center percent12">
								<div class="panel mini-boxsye <?= $allot_3p_s['bgcolor'];?>">
								allot_3p<br />
								<span><?= !empty($allot_3p_s['ip'])?$allot_3p_s['ip'].' / ':'';?><?= !empty($allot_3p_s['time'])?sprintf("%.2f",$allot_3p_s['time']):'';?>
								</div>
							</div>	
							<div class="col-lg-2 text-center percent12">
								<div class="panel mini-boxsye <?= $shenxunfu_3p_s['bgcolor'];?>">
								shenxunfu_3p<br />
								<span><?= !empty($shenxunfu_3p_s['ip'])?$shenxunfu_3p_s['ip'].' / ':'';?><?= !empty($shenxunfu_3p_s['time'])?sprintf("%.2f",$shenxunfu_3p_s['time']):'';?>
								</div>
							</div>								
						</div>	
					</div>						
				  </div>					
			</div>			
			
		</div>
	</div>	
</div>
<script>
	function debug(){
		var username=$('input[name="username"]').val();
		$.ajax({
			url:'/report/monitor/debug',
			type:'POST',
			data:'username='+username,
			success:function(res){
				if(res==101){
					$('#error_msg').css('color', 'green');
					$('#error_msg').html("<?= Yii::t('app', 'user inspect data success');?>");
					return false;
				}else if(res==102){
					$('#error_msg').css('color', 'red');
					$('#error_msg').html("<?= Yii::t('app', 'has no the user');?>");
					return false;
				}else{
					$('#error_msg').css('color', 'red');
					$('#error_msg').html("<?= Yii::t('app', 'user inspect data failed');?>");
					return false;
				}
				//window.location.reload();
			}
		})
	}
</script>