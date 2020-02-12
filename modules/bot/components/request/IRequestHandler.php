<?php
namespace app\modules\bot\components\request;

interface IRequestHandler
{
	public function getFrom($update);
	public function getCommandText($update);
}