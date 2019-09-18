<?php
require_once 'common.php';

function doBootstrap() {

	$errors = array();
	# need tmp_name -a temporary name create for the file and stored inside apache temporary folder- for proper read address
	$zip_file = $_FILES["bootstrap-file"]["tmp_name"];

	# Get temp dir on system for uploading
	$temp_dir = sys_get_temp_dir();

	# keep track of number of lines successfully processed for each file
	$pokemon_processed=0;
	$pokemon_type_processed=0;
	$User_processed=0;

	# check file size
	if ($_FILES["bootstrap-file"]["size"] <= 0)
		$errors[] = "input files not found";

	else {
		
		$zip = new ZipArchive;
		$res = $zip->open($zip_file);

		if ($res === TRUE) {
			$zip->extractTo($temp_dir);
			$zip->close();
		
			$pokemon_path = "$temp_dir/pokemon.csv";
			$pokemon_type_path = "$temp_dir/pokemon_type.csv";
			$User_path = "$temp_dir/User.csv";
			
			$pokemon_type = @fopen($pokemon_type_path, "r");
			$pokemon = @fopen($pokemon_path, "r");
			$User = @fopen($User_path, "r");
			
			if (empty($pokemon) || empty($pokemon_type) || empty($User)){
				$errors[] = "input files not found";
				if (!empty($pokemon)){
					fclose($pokemon);
					@unlink($pokemon_path);
				} 
				
				if (!empty($pokemon_type)) {
					fclose($pokemon_type);
					@unlink($pokemon_type_path);
				}
				
				if (!empty($User)) {
					fclose($User);
					@unlink($User_path);
				}
				
				
			}
			else {
				$connMgr = new ConnectionManager();
				$conn = $connMgr->getConnection();

				# start processing
				
				# truncate current SQL tables

				# then read each csv file line by line (remember to skip the header)
				# $data = fgetcsv($file) gets you the next line of the CSV file which will be stored 
				# in the array $data
				# $data[0] is the first element in the csv row, $data[1] is the 2nd, ....

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