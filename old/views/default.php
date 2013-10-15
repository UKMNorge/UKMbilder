<style type="text/css">
	div#left {
		margin-top:10px;
		width: 400px;
		float:left;
		padding-right: 15px;
	}
	div#right {
		margin-top:10px;
		padding-left: 20px;
		border-left: 1px solid #666;
		width: 400px;
		float: left;
	}
	table {
		width: 380px;
		/*border: 1px solid #666;*/
	}
	table th {
	}
	table td {
		padding:3px;
	}
	table a {
		color: #21759B;
		text-decoration: none;
	}

</style>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.6.1.min.js"></script>
<script type="text/javascript">
/*	$(document).ready(function(){
		$("#table1 tr:odd").css( 'background', '#ddd' );
		$("#table2 tr:odd").css( 'background', '#ddd' );
	});
*/
</script>
<div class="wrap">
<div id="icon-upload" class="icon32"><br /></div>
<h2>Last opp bilder fra m&oslash;nstringen</h2>
<p>P&aring; denne siden kan du laste opp mange bilder p&aring; én gang, knyttet til innslag p&aring; m&oslash;nstringen din</p>
<p><strong>OBS:</strong> Skal du laste opp kun noen bilder til bruk i et innlegg, gj&oslash;r du dette fra innlegget</p>
<!-- <p>Skal du laste opp bilder til et helt frittst&aring;ende album, velger du artikkel/innlegg.</p> -->

<div id="left">
<!--	<h3>En forestilling/hendelse</h3> -->
	
	<table id="table1" class="widefat">
		<thead>
		<tr>
			<th colspan="2">Velg hendelse</th>
		</tr>
		</thead>
		<?php 
			if( count( $this->getData( 'concerts' ) ) > 0 )
				foreach( $this->getData( 'concerts' ) as $concert ):
		?>
		<tr>
			<td>
				<a href="?page=UKM_images&c=upload&event=<?=$concert['c_id']?>" style="font-weight: bold;"><?=$concert['c_name']?></a>
				<div class="row-actions">
					<span class="edit">
						<a href="?page=UKM_images&c=upload&event=<?=$concert['c_id']?>" title="Last opp bilder">Last opp bilder</a>
					</span>
					|
					<span class="view">
						<a href="?page=UKM_images&c=pictures&a=overview&event=<?=$concert['c_id']?>" title="Se bilder">Se opplastede bilder</a>
					</span>
				</div>
			</td>
		</tr>	
		<?php 
				endforeach;
		?>
	</table>
	
</div>
<?php
/*
<div id="right">
	<h3>En artikkel/innlegg</h3>
	<p>Du kan samle alle bilder som h&oslash;rer til en artikkel i et album. N&aring;r du skriver artikkelen kan du koble sammen artikkelen og albumet.</p>
	
	<table id="table2" class="widefat">
		<thead><tr>
			<th>Velg album</th>
		</tr></thead>
		<?php if(is_array($this->getData('albums'))) foreach( $this->getData('albums') as $album ): ?>
		<tr>
			<td>
				<a href="?page=UKM_images&c=upload&album=<?=$album['a_id']?>" style="font-weight: bold;"><?=utf8_encode($album['a_name'])?></a>
				<div class="row-actions">
				<span class="edit">
	            	<a href="?page=UKM_images&c=upload&album=<?=$album['a_id']?>">Last opp bilder</a>
				</span>
				|
				<span class="view">
	            	<a href="?page=UKM_images&c=pictures&a=overview&album=<?=$album['a_id']?>">Se opplastede bilder</a>
				</span>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
	
	<p><br />... Eller opprett nytt album:</p>
	Navn: <form method="POST" action="?page=UKM_images&a=create"><input type="text" name="album_name" /> <input type="submit" value="Opprett" /></form>
	<p><i>(Husk &aring; gi albumet et beskrivende navn som er lett &aring; finne igjen.</i></p>
</div>
</div>
*/?>