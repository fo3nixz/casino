<?
require_once('../../skvCore/Validate.php');
use \skvCore\Validate as Validate;

$v = new Validate();
if(isset($paramName)) {
	return $v->validateParam($params, $paramName);
}
else {
	return $v->validateParams($params);
}
