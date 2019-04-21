<?php namespace Ffdb;

class Filesystem
{

    /**
     * read
     *
     *
     */
    public static function read($path)
    {
        try {
            return file_get_contents($path);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Writes data to the filesystem.
     *
     * @param  string $path The absolute file path to write to
     * @param  string $contents The contents of the file to write
     *
     * @return boolean          Returns true if write was successful, false if not.
     */
    public static function write($path, $contents)
    {
        $last_separator_position = mb_strrpos($path, DIRECTORY_SEPARATOR);

        if ($last_separator_position !== false) {

            $directory = mb_substr($path, 0, $last_separator_position);

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }
        }

        $fp = fopen($path, 'w+');

        if (!flock($fp, LOCK_EX)) {
            return false;
        }

        $result = fwrite($fp, $contents);

        flock($fp, LOCK_UN);
        fclose($fp);

        return $result !== false;
    }

    /**
     * delete
     *
     * @param string $path
     *
     * @return boolean True if deleted, false if not.
     */
    public static function delete($path)
    {
        if (!file_exists($path)) {
            return true;
        }

        return unlink($path);
    }

    /**
     * Validates the name of the file to ensure it can be stored in the
     * filesystem.
     *
     * @param string $name The name to validate against
     * @param boolean $safe_filename Allows filename to be converted if fails validation
     *
     * @return bool Returns true if valid. Throws an exception if not.
     * @throws \Exception
     */
    public static function validateName($name, $safe_filename)
    {
        if (!preg_match('/^[0-9A-Za-z\_\-' . "\\" . DIRECTORY_SEPARATOR . ']{1,63}$/', $name)) {
            if ($safe_filename === true) {
                // rename the file
                $name = preg_replace('/[^0-9A-Za-z\_\-' . '/^[0-9A-Za-z\_\-' . "\\" . DIRECTORY_SEPARATOR . ']{1,63}$/' . ']/', '', $name);

                // limit the file name size
                $name = substr($name, 0, 63);
            } else {
                throw new \Exception(sprintf('`%s` is not a valid file name.', $name));
            }
        }

        return $name;
    }

    /**
     * Get array of stripped file names. Relative paths without extensions
     *
     * @param string $path
     * @param string $ext
     * @return array File names
     */
    public static function getAllFiles(string $path = '', string $ext = 'json')
    {

        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $path_pattern = addslashes($path);

        $ext = '.' . ltrim($ext, '.');
        $ext_pattern = "\\{$ext}";

        $file_names = [];

        $directory = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = new \RegexIterator($iterator, "#^{$path_pattern}(?<name>.+){$ext_pattern}$#i", \RecursiveRegexIterator::GET_MATCH);

        foreach ($files as $file) {
            $file_names[] = $file['name'];
        }

        return $file_names;
    }

}
