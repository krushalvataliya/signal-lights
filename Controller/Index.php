<?php 
require_once 'Controller/Core/Action.php';

class Controller_Index extends Controller_Core_Action
{
	public function IndexAction()
	{	
		$this->getTemplete('index.phtml');
	}

	public function startAction()
	{
		$request = $this->getRequest();
		if ($request->isPost()) {
			$postData = $request->getPost();
			$validate = $this->validateData($postData);
			if ($validate['status'] == 'error') {
				echo json_encode($validate);
				die;
			}

			$signals = [$postData['a'], $postData['b'], $postData['c'], $postData['d']];
			$signalString = implode(',', $signals);
			$sql = "UPDATE `signals` SET `sequence`= '". $signalString ."', `green_interval` = ". $postData['greenInterval'] .",`yellow_interval` = ". $postData['yellowInterval'] .",`status` = '1' WHERE id = 1;";
			if ($this->getAdapter()->update($sql)) {
				$response = ['status' => 'success'];
			}
			else {
				$response = ['status' => 'error', 'message' => 'data not updated'];
			}
			echo json_encode($response);
		}
	}

	public function validateData($data)
	{
		if (!isset($data['greenInterval']) || (int)$data['greenInterval'] <= 0) {
			return ['status' => 'error', 'message' => 'Invalid green interval'];
		}

		if (!isset($data['yellowInterval']) || (int)$data['yellowInterval'] <= 0) {
			return ['status' => 'error', 'message' => 'Invalid yellow interval'];
		}

		$signals = [$data['a'], $data['b'], $data['c'], $data['d']];
		foreach ($signals as $signal) {
			if ((int)$signal < 1 || (int)$signal > 4) {
				return ['status' => 'error', 'message' => 'Sequence must be beetwen 1 and 4'];
			}
		}

		if (count($signals) !== count(array_unique($signals))) {
			return ['status' => 'error', 'message' => 'Duplicate sequence'];
		}
		return ['status' => 'sucess', 'message' => 'data validated'];
		
	}

	public function stopAction()
	{
		$sql = "UPDATE `signals` SET `status` = '0' WHERE `signals`.`id` = 1;";
		if ($this->getAdapter()->update($sql)) {
			$response = ['status' => 'success'];
		}else{
			$response = ['status' => 'error'];
		}
		echo json_encode($response);
	}

	public function getSignalData()
	{
		$sql = "SELECT * FROM `signals` WHERE id = 1";
		$data = $this->getAdapter()->fetchRow($sql);
		list($a, $b, $c, $d) = explode(',', $data['sequence']);
		return [
			'a' => $a,
			'b' => $b,
			'c' => $c,
			'd' => $d,
			'green_interval' => $data['green_interval'],
			'yellow_interval' => $data['yellow_interval']
		];	
	}
}

?>