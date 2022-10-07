<?php

namespace Classes\ITCMedia;

class SitemapFile extends \Bitrix\Seo\SitemapFile {
    const ENTRY_TPL = '<url><loc>%s</loc><lastmod>%s</lastmod></url>';

    public function __construct($fileName, $settings)
	{
        if($settings['PROTOCOL']) {
            $settings['PROTOCOL'] = str_replace('://', '', $settings['PROTOCOL']);
        }

        parent::__construct($fileName, $settings);
    }

    public function addEntry($entry)
	{
		if ($this->isSplitNeeded())
		{
			$this->split();
			$this->addEntry($entry);
		}
		else
		{
			if (!$this->partChanged)
			{
				$this->addHeader();
			}

			$this->putContents(
				sprintf(
					self::ENTRY_TPL,
					\Bitrix\Main\Text\Converter::getXmlConverter()->encode($entry['XML_LOC']),
					\Bitrix\Main\Text\Converter::getXmlConverter()->encode($entry['XML_LASTMOD']),
                    \Bitrix\Main\Text\Converter::getXmlConverter()->encode($entry['XML_CHANGEFREQ']),
                    \Bitrix\Main\Text\Converter::getXmlConverter()->encode($entry['XML_PRIORITY'])
				), self::APPEND
			);
		}
	}

    public function getFilePath()
	{
		return $this->getFileUrl($this);
	}
}