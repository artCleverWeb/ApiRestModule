<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Заказы на Бирже Гринвилль");
$APPLICATION->SetPageProperty("description", "Заказы на Бирже Гринвилль");
$APPLICATION->SetTitle("Заказы на Бирже Гринвилль");
?><?$APPLICATION->IncludeComponent(
	"kolos.studio:personal",
	"",
	[]
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>