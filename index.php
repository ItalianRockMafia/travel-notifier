<!doctype html>
<html>
	<head>
	</head>
	<body>

	<?php
	$config =	require('config.php');
		require 'functions/apicalls.php';

		$users = json_decode(getCall($config->apiUrl . "userStation?transform=1"), true);

		foreach($users["userStation"] as $userdata){
		$response = json_decode(getCall("http://transport.opendata.ch/v1/connections?from="  . $userdata["station"] . "&to=Baden"));

		if ($response->from) {
			$from = $response->from->name;
		}
		if ($response->to) {
			$to = $response->to->name;
		}
		if (isset($response->stations->from[0])) {
			if ($response->stations->from[0]->score < 101) {
				foreach (array_slice($response->stations->from, 1, 3) as $station) {
					if ($station->score > 97) {
						$stationsFrom[] = $station->name;
					}
				}
			}
		}
		if (isset($response->stations->to[0])) {
			if ($response->stations->to[0]->score < 101) {
				foreach (array_slice($response->stations->to, 1, 3) as $station) {
					if ($station->score > 97) {
						$stationsTo[] = $station->name;
					}
				}
			}
		}

		foreach ($response->connections as $connection): ?>
		<?php $j++; ?>
		<tbody>
			<tr class="connection"<?php if ($j == $c): ?> style="display: none;"<?php endif; ?> data-c="<?php echo $j; ?>">
				<td>
					<?php echo date('H:i', strtotime($connection->from->departure)); ?>
					<?php if ($connection->from->delay): ?>
						<span style="color: #a20d0d;"><?php echo '+'.$connection->from->delay; ?></span>
					<?php endif; ?>
					<br/>
					<?php echo date('H:i', strtotime($connection->to->arrival)); ?>
					<?php if ($connection->to->delay): ?>
						<span style="color: #a20d0d;"><?php echo '+'.$connection->to->delay; ?></span>
					<?php endif; ?>
				</td>
				<td>
					<?php echo (substr($connection->duration, 0, 2) > 0) ? htmlentities(trim(substr($connection->duration, 0, 2), '0')).'d ' : ''; ?>
					<?php echo htmlentities(trim(substr($connection->duration, 3, 1), '0').substr($connection->duration, 4, 4)); ?>′<br/>
					<span class="muted">
					<?php echo htmlentities(implode(', ', $connection->products)); ?>
					</span>
				</td>
				<td>
					<?php if ($connection->from->prognosis->platform): ?>
						<span style="color: #a20d0d;"><?php echo htmlentities($connection->from->prognosis->platform, ENT_QUOTES, 'UTF-8'); ?></span>
					<?php else: ?>
						<?php echo htmlentities($connection->from->platform, ENT_QUOTES, 'UTF-8'); ?>
					<?php endif; ?>
					<br/>
					<?php if ($connection->capacity2nd > 0): ?>
						<small title="Expected occupancy 2nd class">
							<?php for ($i = 0; $i < 3; $i++): ?>
								<?php if ($i < $connection->capacity2nd): ?>
									<span class="glyphicon glyphicon-user text-muted"></span>
								<?php else: ?>
									<span class="glyphicon glyphicon-user text-disabled"></span>
								<?php endif; ?>
							<?php endfor; ?>
						</small>
					<?php endif; ?>
				</td>
			</tr>
			<?php $i = 0; foreach ($connection->sections as $section): ?>
				<tr class="section"<?php if ($j != $c): ?> style="display: none;"<?php endif; ?>>
					<td rowspan="2">
						<?php echo date('H:i', strtotime($section->departure->departure)); ?>
						<?php if ($section->departure->delay): ?>
							<span style="color: #a20d0d;"><?php echo '+'.$section->departure->delay; ?></span>
						<?php endif; ?>
					</td>
					<td>
						<?php echo htmlentities($section->departure->station->name, ENT_QUOTES, 'UTF-8'); ?>
					</td>
					<td>
						<?php if ($section->departure->prognosis->platform): ?>
							<span style="color: #a20d0d;"><?php echo htmlentities($section->departure->prognosis->platform, ENT_QUOTES, 'UTF-8'); ?></span>
						<?php else: ?>
							<?php echo htmlentities($section->departure->platform, ENT_QUOTES, 'UTF-8'); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr class="section"<?php if ($j != $c): ?> style="display: none;"<?php endif; ?>>
					<td style="border-top: 0; padding: 4px 8px;">
						<span class="muted">
						<?php if ($section->journey): ?>
							<?php echo htmlentities($section->journey->name, ENT_QUOTES, 'UTF-8'); ?>
						<?php else: ?>
							Walk
						<?php endif; ?>
						</span>
					</td>
					<td style="border-top: 0; padding: 4px 8px;">
						<small title="Expected occupancy 2nd class">
							<?php if ($section->journey && $section->journey->capacity2nd > 0): ?>
								<?php for ($i = 0; $i < 3; $i++): ?>
									<?php if ($i < $section->journey->capacity2nd): ?>
										<span class="glyphicon glyphicon-user text-muted"></span>
									<?php else: ?>
										<span class="glyphicon glyphicon-user text-disabled"></span>
									<?php endif; ?>
								<?php endfor; ?>
							<?php endif; ?>
						</small>
					</td>
				</tr>
				<tr class="section"<?php if ($j != $c): ?> style="display: none;"<?php endif; ?>>
					<td style="border-top: 0;">
						<?php echo date('H:i', strtotime($section->arrival->arrival)); ?>
						<?php if ($section->arrival->delay): ?>
							<span style="color: #a20d0d;"><?php echo '+'.$section->arrival->delay; ?></span>
						<?php endif; ?>
					</td>
					<td style="border-top: 0;">
						<?php echo htmlentities($section->arrival->station->name, ENT_QUOTES, 'UTF-8'); ?>
					</td>
					<td style="border-top: 0;">
						<?php if ($section->arrival->prognosis->platform): ?>
							<span style="color: #a20d0d;"><?php echo htmlentities($section->arrival->prognosis->platform, ENT_QUOTES, 'UTF-8'); ?></span>
						<?php else: ?>
							<?php echo htmlentities($section->arrival->platform, ENT_QUOTES, 'UTF-8'); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	<?php endforeach; ?>
</table>

	
	<?php	}?>
		

		
	

	</body>
</html>