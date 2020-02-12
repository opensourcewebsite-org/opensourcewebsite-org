<?php
namespace app\modules\bot\components;

class ReplyKeyboardManager
{
    const REPLYKEYBOARDBUTTON_IS_CONSTANT = 'isConstants';

    static private $instance;

    private $oldKeyboardButtons = [];
    private $keyboardButtons = [];
    private $_isChanged = FALSE;

    private function __construct(array $keyboardButtons = [])
    {
        $this->oldKeyboardButtons = $this->keyboardButtons = $keyboardButtons;
        foreach ($this->keyboardButtons as $rowIndex => $row)
        {
            foreach ($row as $columnIndex => $keyboardButton)
            {
                if (empty($keyboardButton[ReplyKeyboardManager::REPLYKEYBOARDBUTTON_IS_CONSTANT]))
                {
                    $this->removeButton($rowIndex, $columnIndex);
                }
            }
        }
    }

    static public function getInstance()
    {
        return self::$instance; 
    }

    static public function init(array $keyboardButtons = [])
    {
        if (!isset(self::$instance))
        {
            self::$instance = new ReplyKeyboardManager($keyboardButtons);
        }
    }

    public function removeKeyboardButton(string $text)
    {
        foreach ($this->keyboardButtons as $rowIndex => $keyboardRow)
        {
            foreach ($keyboardRow as $columnIndex => $keyboardButton)
            {
                if ($keyboardButton['text'] == $text)
                {
                    $this->removeButton($rowIndex, $columnIndex);
                }
            }
        }
    }

    public function addKeyboardButton(int $row, array $keyboardButton)
    {
        $this->removeKeyboardButton($keyboardButton['text']);
        if (is_array($this->keyboardButtons[$row]))
        {
            $this->keyboardButtons[$row] = array_merge($this->keyboardButtons[$row], [ $keyboardButton ]);
        }
        else
        {
            $this->keyboardButtons[$row] = [ $keyboardButton ];
        }
        $this->_isChanged = TRUE;
    }

    public function getKeyboardButtons()
    {
        return $this->keyboardButtons;
    }

    public function isChanged()
    {
        return $this->_isChanged;
    }

    private function removeButton(int $rowIndex, int $columnIndex)
    {
        unset($this->keyboardButtons[$rowIndex][$columnIndex]);
        if (empty($this->keyboardButtons[$rowIndex]))
        {
            unset($this->keyboardButtons[$rowIndex]);
        }
        $this->_isChanged = TRUE;
    }
}
