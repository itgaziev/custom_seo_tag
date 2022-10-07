<?php

namespace Classes\ITCMedia;
use Bitrix\Main\Text\Converter;

class SitemapIndex extends \Bitrix\Seo\SitemapIndex
{
    const ENTRY_TPL_SEARCH = '<sitemap><loc>%s</loc>';

    public function __construct($fileName, $settings)
	{
        if($settings['PROTOCOL']) {
            $settings['PROTOCOL'] = str_replace('://', '', $settings['PROTOCOL']);
        }
		
        parent::__construct($fileName, $settings);
    }

    public function removeEntryByUrl($url)
    {
        $fileName = $this->partFile;
        $pattern = sprintf(self::ENTRY_TPL_SEARCH, $url);

        while ($this->isExists()) {
            $c = $this->getContents();
            $p = strpos($c, $pattern);
            unset($c);

            if ($p !== false) {
                $fd = $this->open('r+');

                fseek($fd, intval($p));
                fwrite($fd, str_repeat(" ", strlen(sprintf(
                        self::ENTRY_TPL, Converter::getXmlConverter()->encode($url), Converter::getXmlConverter()->encode(date('c'))
                ))));
                fclose($fd);
                break;
            }

            if (!$this->isSplitNeeded()) {
                break;
            } else {
                $this->part++;
                $fileName = substr($fileName, 0, -strlen(self::FILE_EXT)) . self::FILE_PART_SUFFIX . $this->part . substr($fileName, -strlen(self::FILE_EXT));
                $this->reInit($fileName);
            }
        }

        return $fileName;
    }

    public function validateFileSitemapIndex()
    {
        if (!file_exists($this->pathPhysical)) {
            return true;
        }

        $xml = new \SimpleXMLElement(file_get_contents($this->pathPhysical));

        if($xml->getName() == 'sitemapindex') {
            return true;
        }

        return false;
    }

    public function getSitemapUrl()
    {
        $url = $this->settings['PROTOCOL'] . '://' . \CBXPunycode::toASCII($this->settings['DOMAIN'], $e = NULL) . $this->getFileUrl($this);

        return $url;
    }
}