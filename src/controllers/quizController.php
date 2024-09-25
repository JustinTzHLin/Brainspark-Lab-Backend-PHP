<?php

Class QuizController {
  public function store_result (&$TEMP_DATA){
    try {
      // Get needed data from input
      $json_data = json_decode(file_get_contents('php://input'), true);
      $question_number = $json_data['questionNumber'] ?? null;
      $category = $json_data['category'] ?? null;
      $difficulty = $json_data['difficulty'] ?? null;
      $question_type = $json_data['questionType'] ?? null;
      $multiple = $json_data['multiple'] ?? null;
      $question = $json_data['question'] ?? null;
      $data_array = $json_data['dataArray'] ?? null;
      $answer_array = $json_data['answerArray'] ?? null;
      $correct_count = $json_data['correctCount'] ?? null;
  
      // Add new row into quizzes table
      $new_quiz_query = 'INSERT INTO quizzes(user_id, question_number, correct_number, category, difficulty, question_type) VALUES($1, $2, $3, $4, $5, $6) Returning *;';
      $new_quiz_row = pg_fetch_all(pg_query_params($this->conn, $new_quiz_query, array($TEMP_DATA['user_id'], $question_number, $correct_count, $category, $difficulty, $question_type)));
      
      // Generate variables for queries
      $quiz_id = $new_quiz_row[0]['id'];
      $new_record = $new_quiz_row[0];
      $new_record['data_array'] = array();
      
      // Declare queries for different situation
      $new_question_quiz_query = 'INSERT INTO question_quiz(quiz_id, question_id) VALUES($1, $2) Returning *;';
      $find_question_query = 'SELECT * FROM questions WHERE content=$1 AND correct_answer=$2 AND user_answer=$3 AND incorrect_answer=$4 AND category=$5 AND difficulty=$6 AND question_type=$7';
      $new_question_query = 'INSERT INTO questions(content, correct_answer, user_answer, incorrect_answer, category, difficulty, question_type) VALUES($1, $2, $3, $4, $5, $6, $7) Returning *;';

      // Iterate through every question
      for ($i = 0; $i < Count($data_array); $i++) {
        $question_id;

        // Check if question is existed in database
        $findQuestionRow = pg_fetch_all(pg_query_params($this->conn, $find_question_query, array($data_array[$i]['question'], $data_array[$i]['correct_answer'], $answer_array[$i], json_encode($data_array[$i]['incorrect_answers']), $data_array[$i]['category'], $data_array[$i]['difficulty'], $data_array[$i]['type'])));
        if (Count($findQuestionRow) > 0) {
          $question_id = $findQuestionRow[0]['id'];
          array_push($new_record['data_array'], $findQuestionRow[0]);
        } else {

          // If question is not existed, add new row into questions table
          $new_question_row = pg_fetch_all(pg_query_params($this->conn, $new_question_query, array($data_array[$i]['question'], $data_array[$i]['correct_answer'], $answer_array[$i], json_encode($data_array[$i]['incorrect_answers']), $data_array[$i]['category'], $data_array[$i]['difficulty'], $data_array[$i]['type'])));
          $question_id = $new_question_row[0]['id'];
          array_push($new_record['data_array'], $new_question_row[0]);
        }

        // Add new row into question_quiz table
        $new_question_quiz_row = pg_fetch_all(pg_query_params($this->conn, $new_question_quiz_query, array($quiz_id, $question_id)));
      }

      // Generate variables for next middleware
      $TEMP_DATA['new_record'] = $new_record;
      pg_close($this->conn);
    } catch (Exception $e) {
      pg_close($this->conn);
      $this->error_handler($e->getMessage(), array($json_data));
    }
  }

  public function error_handler ($message, $variable_array) {
    error_log("Error Message: " . $message . " Variables: " . json_encode($variable_array));
    http_response_code(200);
    echo json_encode([
      "success" => false,
      "error" => $message,
      "variables" => $variable_array
    ]);
    exit;
  }
}

?>