// Fleet management JS Script
$(function() {

    // Initialize Bootstrap Checbox
    $('.bscheckbox').checkbox({
        checkedClass: 'fa fa-check-square-o',
        uncheckedClass: 'fa fa-square-o'
    });

});

// AngularJS Simple Comments
function FrmController($scope) {
    $scope.comment = [];
    $scope.btn_add = function() {
        if($scope.txtcomment !=''){
        $scope.comment.push($scope.txtcomment);
        $scope.txtcomment = "";
        }
    }

    $scope.remItem = function($index) {
        $scope.comment.splice($index, 1);
    }
}

// AngularJS Simple Task 
function TodoCtrl($scope) {
  
	$scope.todos = [
		{text:'Learn AngularJS', done:false},         
		{text: 'Build an app', done:false}
	];

	$scope.getTotalTodos = function () {
		return $scope.todos.length;
	};


	$scope.addTodo = function () {
		$scope.todos.push({text:$scope.formTodoText, done:false});
		$scope.formTodoText = '';
	};

	$scope.clearCompleted = function () {
		$scope.todos = _.filter($scope.todos, function(todo){
			return !todo.done;
		});
	};
}

function odometerController($scope) {
	$scope.vehcileOdometer = {
		value: '150,325',
		lastValue: '150,325',
		lastUpdate: '02.07.2014'
	}
}

function vehicleStatusController($scope) {
	$scope.vehicleStatus = {
		value: 'Active'
	}
}