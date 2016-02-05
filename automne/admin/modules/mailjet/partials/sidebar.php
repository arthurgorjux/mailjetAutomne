<?php
$pages = array('Campagnes' => 'campaign', 'Listes' => 'list', 'Segments' => 'segment');

?>

			<div id="sidebar">
				<?php if($api):?>
					<ul>
						<?php foreach ($pages as $object_name => $url) :?>
							<li>
								<a href="/automne/admin/modules/mailjet/<?php echo $url?>/index.php">
									<i class="icon-th-list"></i>
									<span><?php echo $object_name?></span>
								</a>
							</li>
						<?php endforeach;?>
					</ul>
				<?php endif;?>
		</div>