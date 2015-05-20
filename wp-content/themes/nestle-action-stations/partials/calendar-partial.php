<table class="calendar-table">

	<thead>

		<tr class="days">

			<th scope="col">Monday</th>
			<th scope="col">Tuesday</th>
			<th scope="col">Wednesday</th>
			<th scope="col">Thursday</th>
			<th scope="col">Friday</th>
			<th scope="col">Saturday</th>
			<th scope="col">Sunday</th>

		</tr>

	</thead>

	<tbody>

		<?php if( get_field('bar_calendar_header_status') === 'enable' && have_rows('bar_calendar_header') ): ?>

			<tr class="day-themes">

				<?php while( have_rows('bar_calendar_header') ): the_row();

					$monday_theme_obj 	  	=  	get_sub_field('monday');
					$monday_theme 	  	  	=	$monday_theme_obj->name;

					$tuesday_theme_obj 	  	=  	get_sub_field('tuesday');
					$tuesday_theme 	  	  	=	$tuesday_theme_obj->name;

					$wednesday_theme_obj  	=  	get_sub_field('wednesday');
					$wednesday_theme 	  	=	$wednesday_theme_obj->name;

					$thursday_theme_obj		=  	get_sub_field('thursday');
					$thursday_theme 	  	=	$thursday_theme_obj->name;

					$friday_theme_obj 		= 	get_sub_field('friday');
					$friday_theme 	  		=	$friday_theme_obj->name;

					$saturday_theme_obj 	= 	get_sub_field('saturday');
					$saturday_theme 	  	=	$saturday_theme_obj->name;

					$sunday_theme_obj 		= 	get_sub_field('sunday');
					$sunday_theme 	  		=	$sunday_theme_obj->name;

				?>

					<td><?php echo $monday_theme; ?></td>
					<td><?php echo $tuesday_theme; ?></td>
					<td><?php echo $wednesday_theme; ?></td>
					<td><?php echo $thursday_theme; ?></td>
					<td><?php echo $friday_theme; ?></td>
					<td><?php echo $saturday_theme; ?></td>
					<td><?php echo $sunday_theme; ?></td>

				<?php endwhile; ?>

			</tr>

		<?php endif; ?>

	<?php if( have_rows('bar_calendar_menu') ): ?>

		<?php while( have_rows('bar_calendar_menu') ): the_row();

			$monday_obj 	  	=  	get_sub_field('monday');
			$monday 	  	  	=	$monday_obj->post_title;

			$tuesday_obj 	  	=  	get_sub_field('tuesday');
			$tuesday 	  	  	=	$tuesday_obj->post_title;

			$wednesday_obj  	=  	get_sub_field('wednesday');
			$wednesday 	  		=	$wednesday_obj->post_title;

			$thursday_obj		=  	get_sub_field('thursday');
			$thursday 	  		=	$thursday_obj->post_title;

			$friday_obj 		= 	get_sub_field('friday');
			$friday 	  		=	$friday_obj->post_title;

			$saturday_obj 		= 	get_sub_field('saturday');
			$saturday 	  		=	$saturday_obj->post_title;

			$sunday_obj 		= 	get_sub_field('sunday');
			$sunday 	  		=	$sunday_obj->post_title;

		?>

			<tr>

				<td><?php echo $monday; ?></td>
				<td><?php echo $tuesday; ?></td>
				<td><?php echo $wednesday; ?></td>
				<td><?php echo $thursday; ?></td>
				<td><?php echo $friday; ?></td>
				<td><?php echo $saturday; ?></td>
				<td><?php echo $sunday; ?></td>

			</tr>

		<?php endwhile; ?>

	<?php endif; ?>

	</tbody>

</table>