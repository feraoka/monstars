<?php

/**
 * $Id$
 * データベースアクセス ベースクラス
 */

class MS_DB_Base
{
    private $file; /** データベースファイル */
    private $type; /** データベースタイプ */
    private $dbh; /** データベースハンドラ */
    private $name;

    /**
     * Constructor
     * @param file データベースファイル
     * @param type タイプ
     * @throws Exception ファイルを生成できない
     * @throws Exception ファイルをオープンできない
     */
    function __construct($file, $type = 'sqlite:')
    {
        $this->dbh = NULL;
        $this->file = $file;
        $this->type = $type;
        $this->name = $type . $file;
        try {
            $this->_open();
            if (!file_exists($this->file)) {
                throw new Exception("$this->name: permission error");
            }
        } catch (Exception $e) {
            throw new Exception("$this->name: open error");
        }
        $this->_close();
    }
 
    private function _open()
    {
        if ($this->dbh) {
            throw new Exception("$this->name: is already opened");
        }
        $this->dbh = new PDO("$this->name", '', '');
    }

    private function _close()
    {
        if ($this->dbh == NULL) {
            throw new Exception("$this->name: is not opened");
        }
        $this->dbh = NULL;
    }

    public function query($sql)
    {
        try {
            $this->_open();
            $ans = $this->dbh->query($sql);
            $this->_close();
        } catch (Exception $e) {
            throw new Exception("$this->name: access error");
        }
        return $ans;
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
