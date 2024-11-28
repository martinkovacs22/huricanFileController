<?php

namespace Ini;

class IniController {

    private string $filePath;

    public function __construct($filePath) {
        $this->filePath = $filePath;
    }

    /**
     * Beolvassa az INI fájl tartalmát.
     *
     * @return array
     */
    public function getFileContent(): array {
        if (!file_exists($this->filePath)) {
            throw new \Exception("File not found.");
        }

        // Beolvassuk az INI fájlt
        $data = parse_ini_file($this->filePath, true); // true, hogy szekciók is be legyenek olvasva

        if ($data === false) {
            throw new \Exception("Unable to read INI file.");
        }

        return $data;
    }

    /**
     * Kulcs alapján keres a fájlban.
     *
     * @param string $key
     * @return mixed
     */
    public function findByKey($key) {
        $data = $this->getFileContent();
        return $this->searchKeyRecursively($data, $key);
    }

    /**
     * Rekurzív keresés a szekciók és kulcsok között.
     *
     * @param array $array
     * @param string $key
     * @return mixed
     */
    private function searchKeyRecursively($array, $key) {
        foreach ($array as $currentKey => $value) {
            if ($currentKey === $key) {
                return $value; // Ha megtaláljuk a kulcsot, visszaadjuk az értéket
            }

            // Ha az érték egy másik tömb, akkor rekurzívan keresünk
            if (is_array($value)) {
                $found = $this->searchKeyRecursively($value, $key);
                if ($found !== null) {
                    return $found;
                }
            }
        }
        return null; // Ha nem találjuk a kulcsot
    }

    /**
     * Új kulcs-érték pár hozzáadása az INI fájlhoz.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function addKeyValue($key, $value): bool {
        $data = $this->getFileContent();
        $data[$key] = $value;
        return $this->saveToFile($data);
    }

    /**
     * Kulcs törlése az INI fájlból.
     *
     * @param string $key
     * @return bool
     */
    public function deleteByKey($key): bool {
        $data = $this->getFileContent();

        if (array_key_exists($key, $data)) {
            unset($data[$key]);
            return $this->saveToFile($data);
        }

        throw new \Exception("Key not found in the file.");
    }

    /**
     * INI fájlba történő mentés.
     *
     * @param array $data
     * @return bool
     */
    public function saveToFile(array $data): bool {
        $iniContent = '';
        foreach ($data as $section => $values) {
            // Ha szekciók is vannak, kiírjuk őket
            if (is_array($values)) {
                $iniContent .= "[{$section}]\n";
                foreach ($values as $key => $value) {
                    $iniContent .= "{$key} = \"{$value}\"\n";
                }
            } else {
                $iniContent .= "{$section} = \"{$values}\"\n";
            }
        }

        // A fájlba írás
        if (file_put_contents($this->filePath, $iniContent) === false) {
            throw new \Exception("Unable to write to file.");
        }

        return true;
    }

    /**
     * Fájl méretének lekérése byte-ban.
     *
     * @return int
     */
    public function getFileSize(): int {
        if (!file_exists($this->filePath)) {
            throw new \Exception("File not found.");
        }

        return filesize($this->filePath);
    }

    /**
     * Fájl ürességének ellenőrzése.
     *
     * @return bool
     */
    public function isFileEmpty(): bool {
        return filesize($this->filePath) === 0;
    }

    /**
     * Fájl elérési útja getter.
     *
     * @return string
     */
    public function getFilePath(): string {
        return $this->filePath;
    }

    /**
     * Fájl elérési útja setter.
     *
     * @param string $filePath
     * @return self
     */
    public function setFilePath(string $filePath): self {
        $this->filePath = $filePath;
        return $this;
    }
}
?>
