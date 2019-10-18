<?php
require_once 'csv_validation.php';

function doBootstrap() {

	$errors = array();

	# Get temp dir on system for uploading
	$temp_dir = sys_get_temp_dir();

	# tracks number of rows processed for each csv
	$student_processed = 0;
	$course_processed = 0;
	$section_processed = 0;
	$prereq_processed = 0;
	$course_completed_processed = 0;
	$bid_processed = 0;

	# check file size
	if (empty($_FILES["bootstrap-file"]["size"])) {
		$errors[] = "input files not found";

	} else {
		
		# need tmp_name -a temporary name create for the file and stored inside apache temporary folder- for proper read address
		$zip_file = $_FILES["bootstrap-file"]["tmp_name"];

		$zip = new ZipArchive;
		$res = $zip->open($zip_file);

		if ($res === TRUE) {
			$zip->extractTo($temp_dir);
			$zip->close();
		
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
					$fileEmpty = true;
					break;
				}
			}
			
			// close and unlink all files and its paths if any csv file is empty
			if ($fileEmpty){
				$errors[] = "input files not found";
				foreach($files as $type => $file) {
					if (!empty($file)) {
						fclose($file); 
						@unlink($file_paths[$type]);
					}
				}

			} else {
				$connMgr = new ConnectionManager();
				$conn = $connMgr->getConnection();

				// remove all records in enrolment DAO
				$enrolment_dao = new EnrolmentDAO();
				$enrolment_dao->removeAll();

				// start the round 
				$round_dao = new RoundDAO();
				$round_dao->removeAll();
				$round_dao->add(1, "ACTIVE");

				// ============================ STUDENT VALIDATION ===============================
				$student_dao = new StudentDAO();
				$student_dao->removeAll();

				$row_num = 2;
		
				// process each line, check for errors, then insert if no errors
				$header = fgetcsv($files["student"]);
				while (($data = fgetcsv($files["student"])) != false) {
					$row = trim_row($data);
					$row_errors = common_validate_row($header, $row);

					// if pass common validations, do file-specific validations
					if (empty($row_errors)) {
						$row_errors = student_validate_row($row);
					} 

					// if pass file-specific validations, add to database, else record error
					if (empty($row_errors)) {
						$student_dao->add(new Student($row[0], $row[1], $row[2], $row[3], $row[4]));
						$student_processed++;
					} else {
						$errors[] = ["file" => "student.csv", "line" => $row_num, "message" => $row_errors];
					}

					$row_num++;
				}
				
				fclose($files["student"]);
				@unlink($file_paths["student"]);
				

				// ============================= COURSE VALIDATION ===============================
				$course_dao = new CourseDAO();
				$course_dao->removeAll();

				$row_num = 2;

				// process each line, check for errors, then insert if no errors
				$header = fgetcsv($files["course"]);
				while (($data = fgetcsv($files["course"])) != false) {
					$row = trim_row($data);
					$row_errors = common_validate_row($header, $row);

					// if pass common validations, do file-specific validations
					if (empty($row_errors)) {
						$row_errors = course_validate_row($row);
					} 

					// if pass file-specific validations, add to database, else record error
					if (empty($row_errors)) {
						$course_dao->add(new Course($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]));
						$course_processed++;
					} else {
						$errors[] = ["file" => "course.csv", "line" => $row_num, "message" => $row_errors];
					}

					$row_num++;
				}

				fclose($files["course"]);
				@unlink($file_paths["course"]);

				// ============================= SECTION VALIDATION ==============================
				$section_dao = new SectionDAO();
				$section_dao->removeAll();

				// re-initialise MinBidDAO as well
				$minbid_dao = new MinBidDAO();
				$minbid_dao->removeAll();
				
				$row_num = 2;
				// process each line, check for errors, then insert if no errors
				$header = fgetcsv($files["section"]);
				while (($data = fgetcsv($files["section"])) != false) {
					$row = trim_row($data);
					$row_errors = common_validate_row($header, $row);

					// if pass common validations, do file-specific validations
					if (empty($row_errors)) {
						$row_errors = section_validate_row($row);
					} 

					// if pass file-specific validations, add to database, else record error
					if (empty($row_errors)) {
						$section_dao->add(new Section($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7]));
						$section_processed++;
						
						// set min bid for each section as 10
						$minbid_dao->add($row[0], $row[1], 10);
					} else {
						$errors[] = ["file" => "section.csv", "line" => $row_num, "message" => $row_errors];
					}

					$row_num++;
				}

				fclose($files["section"]);
				@unlink($file_paths["section"]);


				// ============================= PREREQUISITE VALIDATION ==============================
				$prereq_dao = new PrereqDAO();
				$prereq_dao->removeAll();

				$row_num = 2;

				// process each line, check for errors, then insert if no errors
				$header = fgetcsv($files["prerequisite"]);
				while (($data = fgetcsv($files["prerequisite"])) != false) {
					$row = trim_row($data);
					$row_errors = common_validate_row($header, $row);

					// if pass common validations, do file-specific validations
					if (empty($row_errors)) {
						$row_errors = prereq_validate_row($row);
					} 

					// if pass file-specific validations, add to database, else record error
					if (empty($row_errors)) {
						$prereq_dao->add($row[0], $row[1]);
						$prereq_processed++;
					} else {
						$errors[] = ["file" => "prerequisite.csv", "line" => $row_num, "message" => $row_errors];
					}

					$row_num++;
				}

				fclose($files["prerequisite"]);
				@unlink($file_paths["prerequisite"]);


				// ============================= COURSE COMPLETED VALIDATION ==============================
				$course_completed_dao = new CourseCompletedDAO();
				$course_completed_dao->removeAll();

				$row_num = 2;

				// process each line, check for errors, then insert if no errors
				$header = fgetcsv($files["course_completed"]);
				while (($data = fgetcsv($files["course_completed"])) != false) {
					$row = trim_row($data);
					$row_errors = common_validate_row($header, $row);

					// if pass common validations, do file-specific validations
					if (empty($row_errors)) {
						$row_errors = course_completed_validate_row($row);
					} 

					// if pass file-specific validations, add to database, else record error
					if (empty($row_errors)) {
						$course_completed_dao->add($row[0], $row[1]);
						$course_completed_processed++;
					} else {
						$errors[] = ["file" => "course_completed.csv", "line" => $row_num, "message" => $row_errors];
					}

					$row_num++;
				}

				fclose($files["course_completed"]);
				@unlink($file_paths["course_completed"]);


				// ============================= BID VALIDATION ==============================
				$bid_dao = new BidDAO();
				$bid_dao->removeAll();

				$row_num = 2;

				// process each line, check for errors, then insert if no errors
				$header = fgetcsv($files["bid"]);
				while (($data = fgetcsv($files["bid"])) != false) {
					$row = trim_row($data);
					$row_errors = common_validate_row($header, $row);

					// if pass common validations, do file-specific validations
					if (empty($row_errors)) {
						$row_errors = bid_validate_row($row);
					} 

					// if pass file-specific validations, add to database, else record error
					if (empty($row_errors)) {
						$student = $student_dao->retrieve($row[0]);
						$updatedBal = $student->edollar - $row[1];
						$student_dao->update(new Student($student->userid, $student->password, $student->name, $student->school, $updatedBal));

						$bid_dao->add(new Bid($row[0], $row[1], $row[2], $row[3]));
						$bid_processed++;
					} else {
						$errors[] = ["file" => "bid.csv", "line" => $row_num, "message" => $row_errors];
					}

					$row_num++;
				}
				fclose($files["bid"]);
				@unlink($file_paths["bid"]);
			}
		}
	}


	$result = [ 
		"status" => "success",
		"num-record-loaded" => [
			["bid.csv" => $bid_processed],
			["course.csv" => $course_processed],
			["course_completed.csv" => $course_completed_processed], 
			["prerequisite.csv" => $prereq_processed],
			["section.csv" => $section_processed],
			["student.csv" => $student_processed]
		]
	];

	if (!empty($errors)) {
		// $sortclass = new Sort();
		// if (is_array($errors[0])) {
		// 	$errors = $sortclass->sort_it($errors, "array");
		// } else {
		// 	$errors = $sortclass->sort_it($errors, "string");
		// }
		
		$result["status"] = "error";
		$result["error"] = $errors;
	}

	header('Content-Type: application/json');
	echo json_encode($result, JSON_PRETTY_PRINT);
	exit;
}
?>