<?php
require_once 'common.php';

function doBootstrap() {

	$errors = array();
	# need tmp_name -a temporary name create for the file and stored inside apache temporary folder- for proper read address
	$zip_file = $_FILES["bootstrap-file"]["tmp_name"];

	# Get temp dir on system for uploading
	$temp_dir = sys_get_temp_dir();

	# keep track of number of lines successfully processed for each file
	# check file size
	if ($_FILES["bootstrap-file"]["size"] <= 0)
	if ($_FILES["bootstrap-file"]["size"] <= 0) {
		$errors[] = "input files not found";

	else {
	} else {
		
		$zip = new ZipArchive;
		$res = $zip->open($zip_file);

		if ($res === TRUE) {
			$zip->extractTo($temp_dir);
			$zip->close();
		
			$pokemon_path = "$temp_dir/pokemon.csv";
			// include all csv files in the bootstrap zip
			$file_names = ["student.csv", "course.csv", "section.csv", "prerequisite.csv", "course_completed.csv", "bid.csv"];

			// loop through file_names to generate a list of file paths
			$file_paths = array();
			foreach ($file_names as $file_name) {
				$type = explode(".", $file_name)[0];
				$file_paths[$type] = "$temp_dir/$file_name"; 
			}

			// loop through file_paths to generate a list of opened files
			$files = array();
			foreach ($file_paths as $type => $file_path) {
				$files[$type] = @fopen($file_path, "r");
			}

			// check for empty csv files
			$fileEmpty = false;
			foreach ($files as $type => $file) {
				if (empty($file)) {
					$errors[] = "$type.csv is empty";
					$fileEmpty = true;
				}
				
			}
			else {
			
			// close and unlink all files and its paths if any csv file is empty
			if ($fileEmpty){
				foreach ($files as $type => $file) {
					fclose($file);
					@unlink($file_paths[$type]);
				}

			} else {
				$connMgr = new ConnectionManager();
				$conn = $connMgr->getConnection();

				# start processing
				// ============================ STUDENT VALIDATION ===============================
				$student_dao = new StudentDAO();
				$student_dao->removeAll();

				# then read each csv file line by line (remember to skip the header)
				$row_num = 2;
				$student_processed = 0;

				# process each line and check for errors
				
				# for this lab, assume the only error you should check for is that each CSV field 
				# must not be blank 


				
				# for the project, the full error list is listed in the wiki

				// Pokemon Type
				$pokemonTypeDAO = new PokemonTypeDAO();
				$pokemonTypeDAO->removeAll();
		
				// process each line, check for errors, then insert if no errors
				$data = fgetcsv($pokemon_type);
				while (($data = fgetcsv($pokemon_type)) != false) {
					$pokemonTypeDAO->add($data[0]);
					$pokemon_type_processed++;
				}
				
				// clean up
				fclose($pokemon_type);
				@unlink($pokemon_type_path);
				
				// Pokemon 
				$pokemonDAO = new PokemonDAO();
				$pokemonDAO->removeAll();

				// process each line, check for errors, then insert if no errors
				$data = fgetcsv($pokemon);
				while (($data = fgetcsv($pokemon)) != false) {
					$pokemonDAO->add(new Pokemon($data[0], $data[1]));
					$pokemon_processed++;
				}

				// clean up
				fclose($pokemon);
				@unlink($pokemon_path);

				// User 
				$userDAO = new UserDAO();
				$userDAO->removeAll();

				// process each line, check for errors, then insert if no errors
				$data = fgetcsv($User); // skip header
				while (($data = fgetcsv($User)) != false) {
					$userDAO->add(new User($data[0], $data[1], $data[2], $data[3]));
					$User_processed++;
				}

				// clean up
				fclose($User);
				@unlink($User_path);
			}
		}
	}

	# Sample code for returning JSON format errors. remember this is only for the JSON API. Humans should not get JSON errors.

	if (!isEmpty($errors))
	{	
		$sortclass = new Sort();
		$errors = $sortclass->sort_it($errors,"bootstrap");
		$result = [ 
			"status" => "error",
			"messages" => $errors
		];
	}

	else
	{	
		$result = [ 
			"status" => "success",
			"num-record-loaded" => [
				"pokemon.csv" => $pokemon_processed,
				"pokemon_type.csv" => $pokemon_type_processed,
				"user.csv" => $User_processed
			]
		];
	}
	header('Content-Type: application/json');
	echo json_encode($result, JSON_PRETTY_PRINT);

}
?>