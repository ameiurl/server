<?php
namespace My\Util;
/**
 * @author Funsion Wu
 * @abstract SPL使用案例，全国首发，技术分享，欢迎转帖
 */
class Dir extends \RecursiveDirectoryIterator {
    const CHILD_FIRST = \RecursiveIteratorIterator::CHILD_FIRST ;
    const LEAVES_ONLY = \RecursiveIteratorIterator::LEAVES_ONLY ;
    const SELF_FIRST  = \RecursiveIteratorIterator::SELF_FIRST ;
    /* ideas：将Dir类设置为不变类，无状态类 */
    private static function getDirIterator( $dir, $mode=self::LEAVES_ONLY ) {
        if( !file_exists($dir) ){ exit ; }
        $dirIterator = new \RecursiveDirectoryIterator($dir);
        $objIterator = new \RecursiveIteratorIterator( $dirIterator, $mode );
        return $objIterator;
    }
    /**
     * 递归的删除目录                      
     + ----------------------------------------------------- +
     * @param   $dir 要删除的目录
     * @param   $delSelf 决定删除目录or清空目录，默认删除目录
     */
    public static function delDir( $dir, $delSelf=true ) {
        $dirIterator = self::getDirIterator($dir, self::CHILD_FIRST);
        foreach ( $dirIterator as $file ) {
            if ( $file->isDir() ) {
                 @ rmdir( $file->getRealPath() );
            }else{
                 @ unlink( $file->getRealPath() );
            }
        }
        if( $delSelf ) { @ rmdir($dir); }
    }
    /**
     * 递归的列出目录，遍历目录
     + -------------------------- +
     * @param   $dir 要操作的目录
     */
    public static function listDir ( $dir ) {
        $dirIterator = self::getDirIterator( $dir, self::SELF_FIRST );
        foreach ( $dirIterator as $file ) {
            $filepath = str_replace('\\' , '/' , $file->getPath() );
            $deep = substr_count( $filepath , '/' );
            if( $file->isDir() ) {
                $str .= '<div style="color:blue;margin-left:'. 35*$deep .'px"> + ' ;
                $str .=  $file->getBasename() .'</div>' ;
            }elseif( $file->isFile() ){
                $str .= '<div style="margin-left:'. 35*$deep .'px">' . $file->getBasename() .'</div>';
            }
        }
        return $str ;
    }
    /**
     * 统计目录的信息（总字节数，文件数，目录数）
     + -----------------------------=----------- +
     * @param   $dir 要操作的目录
     * @return  由目录信息组成的数组
     */
    public static function countDir( $dir ) {
        $countDir = $countFiles = $size = 0 ;
        $dirIterator = self::getDirIterator( $dir, self::SELF_FIRST );
        foreach ( $dirIterator as $file ) {
            if( $file->isDir() ) {
                $countDir++ ;
            }elseif( $file->isFile() ){
                $countFiles++ ;
                $size += $file->getSize() ;
            }
        }
        return array( 'countDir'=>$countDir, 'countFiles'=>$countFiles, 'size'=>$size.' Byte' );
    }
    /**
     * 递归的创建目录
     + -------------------- +
     * @param   $dir 要创建的目录
     * @param   $mode 所创建目录的读写权限
     */
     public static function makeDir( $dir, $mode=0644 ) {
        return mkdir( $dir, $mode, true );
     }
}

/* ========================== 调用方法 =========================== */

// Dir::delDir('./need_del_dir');
// echo Dir::listDir('tools');
// var_dump( Dir::countDir('tools') );
// Dir::makeDir( 'aaa/ccc/ddd/eee/fff' );
