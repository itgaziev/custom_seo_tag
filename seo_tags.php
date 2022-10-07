<?php

namespace Classes\ITCMedia;

class SeoTags 
{
    static $iblock_id = 150;
    static $filename = 'sitemap-itcmedia-tags';
    static $sitemapResult = [];

    static public function SearchTagsBySection($SECTION_ID, $IS_BOTTOM = false) {
        $sections = [];
        $SectList = \CIBlockSection::GetList($arSort, array("IBLOCK_ID"=> self::$iblock_id, "ACTIVE"=>"Y", "UF_USE_SECTION" => $SECTION_ID, 'UF_SHOW_BOTTOM' => $IS_BOTTOM) ,false, array("ID","UF_URL_PAGE","UF_TITLE","UF_USE_SECTION", 'UF_SHOW_BOTTOM'));
        
        while ($SectListGet = $SectList->GetNext())
        {
            $sections[$SectListGet['ID']] = $SectListGet;
        }   

        if($sections) {
            $arSelect = array("ID", "NAME", "IBLOCK_SECTION_ID", "PROPERTY_URL_PAGE", "PROPERTY_NEW_URL", "PROPERTY_SEO_TITLE", "PROPERTY_SEO_DESCRIPTION", "PROPERTY_ICONS", "PROPERTY_H1_TITLE");  
            $res = \CIBlockElement::GetList(array('sort' => 'asc'), array("IBLOCK_ID"=> self::$iblock_id, "ACTIVE"=>"Y", "SECTION_ID"=> array_keys($sections)), false, array("nPageSize"=>50), $arSelect);  
            while($arFields = $res->GetNext())  
            {  
                if(isset($sections[$arFields['IBLOCK_SECTION_ID']])) {
                    
                    if($arFields['PROPERTY_ICONS_VALUE']) $arFields['ICON_URL'] = \CFile::GetPath($arFields['PROPERTY_ICONS_VALUE']);

                    $sections[$arFields['IBLOCK_SECTION_ID']]['ELEMENTS'][] = $arFields;
                }
            }

            return $sections;
        }

        return [];
    }

    static public function SearchTagsByUrl($URL) {
        
        $sections = [];
        $SectList = \CIBlockSection::GetList($arSort, array("IBLOCK_ID"=> self::$iblock_id, "ACTIVE"=>"Y", "UF_URL_PAGE" => $URL) ,false, array("ID","UF_URL_PAGE","UF_TITLE","UF_USE_SECTION"));
        
        while ($SectListGet = $SectList->GetNext())
        {
            $sections[$SectListGet['ID']] = $SectListGet;
        }   

        if($sections) {
            $arSelect = array("ID", "NAME", "IBLOCK_SECTION_ID", "PROPERTY_URL_PAGE", "PROPERTY_NEW_URL", "PROPERTY_SEO_TITLE", "PROPERTY_SEO_DESCRIPTION", "PROPERTY_ICONS", "PROPERTY_H1_TITLE");  
            $res = \CIBlockElement::GetList(array('sort' => 'asc'), array("IBLOCK_ID"=> self::$iblock_id, "ACTIVE"=>"Y", "SECTION_ID"=> array_keys($sections)), false, array("nPageSize"=>50), $arSelect);  
            while($arFields = $res->GetNext())  
            {  
                if(isset($sections[$arFields['IBLOCK_SECTION_ID']])) {
                    
                    if($arFields['PROPERTY_ICONS_VALUE']) $arFields['ICON_URL'] = \CFile::GetPath($arFields['PROPERTY_ICONS_VALUE']);

                    $sections[$arFields['IBLOCK_SECTION_ID']]['ELEMENTS'][] = $arFields;
                }
            }

            return $sections;
        }

        return [];
    }

    static public function onPageStart() {
        global $APPLICATION;
        $requestUri = explode('?', $_SERVER['REQUEST_URI'])[0];
        $tag = self::searchNewUrl($requestUri);
        if($tag) {
            $context = \Bitrix\Main\Application::getInstance()->getContext();

            $request = $context->getRequest();
    
            $server = $context->getServer();

            $serverArray = $server->toArray();

            $_SERVER['REQUEST_URI'] = $tag['PROPERTY_URL_PAGE_VALUE'];
    
            $serverArray['REQUEST_URI'] = $tag['PROPERTY_URL_PAGE_VALUE'];

            $server->set($serverArray);
    
            $context->initialize(new \Bitrix\Main\HttpRequest($server, $_GET, [], [], $_COOKIE), $context->getResponse(), $server);
    
            $APPLICATION->reinitPath();

        }

    }

    static private function searchNewUrl($requestUri) {
        \CModule::IncludeModule("iblock");
        
        $arSelect = array("ID", "NAME", "IBLOCK_SECTION_ID", "PROPERTY_URL_PAGE", "PROPERTY_NEW_URL", "PROPERTY_SEO_TITLE", "PROPERTY_SEO_DESCRIPTION", "PROPERTY_H1_TITLE");  
        if($result = \CIBlockElement::GetList(array('sort' => 'asc'), array("IBLOCK_ID"=> self::$iblock_id, "ACTIVE"=>"Y", "PROPERTY_NEW_URL"=> $requestUri), false, array(), $arSelect)->GetNext()) {
            $GLOBALS['ITC_SEO_TAGS'] = $result;
            return $result;
        }  

        return false;
    }

    static public function sitemapIndex() {
        $sitemapFile = self::sitemapFile();

        $sitemapIndexFile = new SitemapIndex('sitemap', [
            'SITE_ID' => SITE_ID,
            'PROTOCOL' => 'https',
            'DOMAIN' => 'sankera.ru',
        ]);

        $sitemapIndexFile->appendIndexEntry($sitemapFile);

    }

    static public function sitemapFile() {
        $sitemapFile = new SitemapFile(self::$filename, [
            'SITE_ID' => SITE_ID,
            'PROTOCOL' => 'https',
            'DOMAIN' => 'sankera.ru',
        ]);

        if ($sitemapFile->isExists()) {
            $sitemapFile->delete();
        }
        
        $sitemapFile->addHeader();

        $arSelect = array("ID", "NAME", 'TIMESTAMP_X', "PROPERTY_URL_PAGE", "PROPERTY_NEW_URL");  
        $res = \CIBlockElement::GetList(array('sort' => 'asc'), array("IBLOCK_ID"=> self::$iblock_id, "ACTIVE"=>"Y", "!PROPERTY_NEW_URL" => false), false, array(), $arSelect);

        while($arFields = $res->GetNext()) {
            $dateChange = new \Bitrix\Main\Type\DateTime;

            if($arFields['TIMESTAMP_X'] && $arFields['TIMESTAMP_X'] instanceof \Bitrix\Main\Type\DateTime) {
                $arFields = $page['TIMESTAMP_X'];
            }

            $sitemapFile->addEntry([
                'XML_LOC' => 'https://sankera.ru' . $arFields['PROPERTY_NEW_URL_VALUE'],
                'XML_LASTMOD' => $dateChange->format('c'),
                // 'XML_CHANGEFREQ' => '',
                // 'XML_PRIORITY' => '0.5',
            ]);
        }

        $sitemapFile->addFooter();

        self::$sitemapResult = [
            'FILE_URL' => $sitemapFile->getUrl(),
            'FILE_PATH' => $sitemapFile->getFilePath()
        ];
        return $sitemapFile;
    }
}