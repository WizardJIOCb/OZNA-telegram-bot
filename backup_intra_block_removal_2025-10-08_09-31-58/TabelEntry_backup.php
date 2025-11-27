<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

/**
 * Tabel Entry class for handling timesheet entries
 * Encapsulates the logic for creating and managing timesheet entries
 */
class TabelEntry
{
    private array $activityTypeMappings = [
        // Standard activity types
        'Дорога' => '🚗 Дорога',
        'Подготовка' => '📋 Подготовка к служебной поездке',
        'Инструктаж' => '📋 Инструктаж',
        'Ожидание' => '⏳ Ожидание',
        'Гарантия' => '🛡️ Гарантия',
        'Простой' => '⏸️ Простой',
        'ШМ_Простой' => '⏸️ Простой (Шеф-монтаж)',
        // TKP preparation
        'ПодготовкаТКП_ОПНВГ' => '🏢 Подготовка ТКП (ОПНВГ)',
        'ПодготовкаТКП_НКО' => '🏢 Подготовка ТКП (НКО)',
        'ПодготовкаТКП_МСО' => '🏢 Подготовка ТКП (МСО)',
        'ПодготовкаТКП_АСН' => '🏢 Подготовка ТКП (АСН)',
        // Commissioning
        'ПНР' => '⚙️ Пусконаладочные работы',
        'УЗ_РК' => '🔧 Устранение замечаний РК',
        'РабГарантии' => '🛡️ Работы по гарантии',
        // Supervision
        'ШМР' => '🔧 Шеф-монтажные работы',
        'ШМ_УЗ_РК' => '🔧 Устранение замечаний РК (Шеф-монтаж)',
        'ШМ_РабГарантии' => '🛡️ Работы по гарантии (Шеф-монтаж)',
        // Supervision works sub-activities
        'ШМР_РаботыПоГрафику' => '📅 Работы по графику',
        'ШМР_ПодготовкаПоездки' => '🚗 Подготовка к служебной поездке',
        'ШМР_Дорога' => '🚗 Дорога',
        'ШМР_Инструктаж' => '📋 Инструктаж',
        'ШМР_СдачаОтчета' => '💰 Сдача авансового отчета',
        // Current activities
        'МедОсмотр' => '👨‍⚕️ Медицинский осмотр',
        'Аттестация' => '📚 Аттестация',
        'ПодготовкаТКП' => '🏢 Подготовка ТКП',
        'Общая' => '📊 Общая деятельность',
        // New current activity types
        'УчастиеВЗПИ' => '🏢 Участие в ЗПИ',
        'РазрПрограммы' => '📝 Разработка программы работ',
        'РазрГрафика' => '📅 Разработка графика работ',
        'ОргДопоставки' => '📦 Организация допоставки',
        'УчастиеВОПИ' => '🏭 Участие в ОПИ',
        'РазработкаПрограммыРабот' => '📝 Разработка программы работ',
        'РазработкаГрафикаРабот' => '📅 Разработка графика работ',
        'ПодготовкаОтчета' => '📊 Подготовка отчета',
        // Supervision project activity types
        'ИспОбМаст' => '👷 Исполнение обязанностей мастера',
        'ШефНаладочныеРаботы' => '🔧 Шеф-наладочные работы',
        // Project Activity "Прочее" type
        'Прочее_ПроектнаяДеятельность' => '📝 Прочее (Проектная деятельность)',
        // Supervision Project Activity "Прочее" type  
        'Прочее_ПроектнаяДеятельность_ШМР' => '📝 Прочее (Проектная деятельность)',
        // Commissioning activities ("Отдел ПНР")
        'КомплексноеОпробование' => '⚙️ Комплексное опробование',
        'РежимнаяНаладка' => '⚙️ Режимная наладка',
        'ПодготовкаКПоездке' => '📋 Подготовка к служебной поездке',
        'СдачаАвансовогоОтчета' => '💰 Сдача авансового отчета',
        'ОхраннаяСигнализация' => '🚨 Охранная сигнализация',
        'ОтоплениеИВентиляция' => '🌡️ Отопление и вентиляция',
        'ТехнологическоеОборудование' => '⚙️ Технологическое оборудование',
        'Метрология' => '📎 Метрология',
        // Mobile brigade
        'Электромонтажные' => '⚡ Электромонтажные работы',
        'Сварочные' => '🔥 Сварочные работы',
        'Слесарные' => '🔧 Слесарные работы',
        'Теплоизоляция' => '🧱 Теплоизоляция',
        'Документы' => '📂 Сдача отчетных документов',
        'АвансовыйОтчет' => '💰 Сдача авансового отчета',
        // New electrical equipment activity types
        'РабПоГр_ЭлектрОб' => '📅 Работы по графику (Электрооборудование)',
        'ФормИД_ЭлектрОб' => '📄 Формирование ИД (Электрооборудование)',
        'ПодтВех_ЭлектрОб' => '✅ Подтвердить достижение вехи (Электрооборудование)',
        'ПодтВех_ПожСигн' => '✅ Подтвердить достижение вехи (Пожарная сигнализация)',
        'ИзмСхПд_ЭлектрОб' => '🔀 Изменение схемы подключения (Электрооборудование, Дополнительные работы)',
        'МонтКбЛн_ЭлектрОб' => '🔌 Прокладка кабеля (Электрооборудование, Дополнительные работы)',
        'РаскКбЛн_ЭлектрОб' => '📊 Подключение кабеля (Электрооборудование, Дополнительные работы)',
        'МонтОб_ЭлектрОб' => '⚙️ Монтаж оборудования (Электрооборудование, Дополнительные работы)',
        'Прочее_ЭлектрОб' => '📝 Прочее (Электрооборудование, Дополнительные работы)',
        // Electrical equipment main category
        'Электрооборудование' => '⚡ Электрооборудование',
        // Automation equipment activity types
        'РабПоГр_Автом' => '📅 Работы по графику (Автоматизация)',
        'ФормИД_Автом' => '📄 Формирование ИД (Автоматизация)',
        'ПодтВех_Автом' => '✅ Подтвердить достижение вехи (Автоматизация)',
        'ДопРаб_Автом' => '🛠️ Дополнительные работы (Автоматизация)',
        'ИзмСх_Автом' => '🔀 Изменение схемы подключения (Автоматизация, Дополнительные работы)',
        'МонтКб_Автом' => '🔌 Прокладка кабеля (Автоматизация, Дополнительные работы)',
        'РаскКб_Автом' => '📊 Подключение кабеля (Автоматизация, Дополнительные работы)',
        'МонтАСУ_Автом' => '🖥️ Монтаж оборудования АСУ ТП (Автоматизация, Дополнительные работы)',
        'УдалПО_Автом' => '📡 Удаленный ПНР ПО (Автоматизация, Дополнительные работы)',
        'Прочее_Автом' => '📝 Прочее (Автоматизация, Дополнительные работы)',
        // Fire alarm equipment activity types
        'РабПоГр_ПожСигн' => '📅 Работы по графику (Пожарная сигнализация)',
        'ФормИД_ПожСигн' => '📄 Формирование ИД (Пожарная сигнализация)',
        'ПодтВех_ПожСигн' => '✅ Подтвердить достижение вехи (Пожарная сигнализация)',
        'ДопРаб_ПожСигн' => '🛠️ Дополнительные работы (Пожарная сигнализация)',
        'ИзмСх_ПожСигн' => '🔀 Изменение схемы подключения (Пожарная сигнализация, Дополнительные работы)',
        'МонтКб_ПожСигн' => '🔌 Прокладка кабеля (Пожарная сигнализация, Дополнительные работы)',
        'РаскКб_ПожСигн' => '📊 Подключение кабеля (Пожарная сигнализация, Дополнительные работы)',
        'МонтОб_ПожСигн' => '⚙️ Монтаж оборудования (Пожарная сигнализация, Дополнительные работы)',
        'Прочее_ПожСигн' => '📝 Прочее (Пожарная сигнализация, Дополнительные работы)',
        // Security alarm equipment activity types
        'РабПоГр_ОхрСигн' => '📅 Работы по графику (Охранная сигнализация)',
        'ФормИД_ОхрСигн' => '📄 Формирование ИД (Охранная сигнализация)',
        'ПодтВех_ОхрСигн' => '✅ Подтвердить достижение вехи (Охранная сигнализация)',
        'ДопРаб_ОхрСигн' => '🛠️ Дополнительные работы (Охранная сигнализация)',
        'ИзмСх_ОхрСигн' => '🔀 Изменение схемы подключения (Охранная сигнализация, Дополнительные работы)',
        'МонтКб_ОхрСигн' => '🔌 Прокладка кабеля (Охранная сигнализация, Дополнительные работы)',
        'РаскКб_ОхрСигн' => '📊 Подключение кабеля (Охранная сигнализация, Дополнительные работы)',
        'МонтОб_ОхрСигн' => '⚙️ Монтаж оборудования (Охранная сигнализация, Дополнительные работы)',
        'Прочее_ОхрСигн' => '📝 Прочее (Охранная сигнализация, Дополнительные работы)',
        // Heating and ventilation equipment activity types
        'РабПоГр_ВентОтопл' => '📅 Работы по графику (Вентиляция и отопление)',
        'ФормИД_ВентОтопл' => '📄 Формирование ИД (Вентиляция и отопление)',
        'ПодтВех_ВентОтопл' => '✅ Подтвердить достижение вехи (Вентиляция и отопление)',
        'ДопРаб_ВентОтопл' => '🛠️ Дополнительные работы (Вентиляция и отопление)',
        'ИзмСх_ВентОтопл' => '🔀 Изменение схемы подключения (Вентиляция и отопление, Дополнительные работы)',
        'МонтКб_ВентОтопл' => '🔌 Прокладка кабеля (Вентиляция и отопление, Дополнительные работы)',
        'РаскКб_ВентОтопл' => '📊 Подключение кабеля (Вентиляция и отопление, Дополнительные работы)',
        'МонтАСУ_ВентОтопл' => '🖥️ Монтаж оборудования АСУ ТП (Вентиляция и отопление, Дополнительные работы)',
        'УдалПО_ВентОтопл' => '📡 Удаленный ПНР ПО (Вентиляция и отопление, Дополнительные работы)',
        'Прочее_ВентОтопл' => '📝 Прочее (Вентиляция и отопление, Дополнительные работы)',
        // Technological equipment activity types
        'РабПоГр_ТехОб' => '📅 Работы по графику (Технологическое оборудование)',
        'ФормИД_ТехОб' => '📄 Формирование ИД (Технологическое оборудование)',
        'ПодтВех_ТехОб' => '✅ Подтвердить достижение вехи (Технологическое оборудование)',
        'ДопРаб_ТехОб' => '🛠️ Дополнительные работы (Технологическое оборудование)',
        // New technological equipment dismantling activities
        'ДемАрм_ТехОб' => '🔧 Демонтаж/ревизия арматуры (Технологическое оборудование, Дополнительные работы)',
        'ДемОб_ТехОб' => '⚙️ Демонтаж/ревизия техн. оборудования (Технологическое оборудование, Дополнительные работы)',
        'Прочее_ТехОб' => '📝 Прочее (Технологическое оборудование, Дополнительные работы)',
        // Metrology activity types  
        'РабПоГр_Метрол' => '📅 Работы по графику (Метрология)',
        'ФормИД_Метрол' => '📄 Формирование ИД (Метрология)',
        'ПодтВех_Метрол' => '✅ Подтвердить достижение вехи (Метрология)',
        'ДопРаб_Метрол' => '🔨 Дополнительные работы (Метрология)',
        // Metrology additional works activities
        'ИзмКонф_Метрол' => '⚙️ Изменение конфигурации СИ (Метрология, Дополнительные работы)',
        'КалПов_Метрол' => '🔍 Калибровка/поверка оборудования (Метрология, Дополнительные работы)',
        'КМХ_Метрол' => '📈 КМХ СИ (Метрология, Дополнительные работы)',
        'КоррХар_Метрол' => '🔧 Коррекция характеристик СИ (Метрология, Дополнительные работы)',
        'Проч_Метрол' => '📝 Прочее (Метрология, Дополнительные работы)',
        // RK comments elimination activity types
        'УстрЗам_ЭС' => '🔌 Устранения замечаний РК (Раздел ЭС)',
        'УстрЗам_АТХ' => '🤖 Устранения замечаний РК (Раздел АТХ)',
        'УстрЗам_ОВКВ' => '❄️ Устранения замечаний РК (Раздел ОВКВ)',
        'УстрЗам_ПСОС' => '🔥 Устранения замечаний РК (Раздел ПС/ОС)',
        'УстрЗам_ТХ' => '⚙️ Устранения замечаний РК (Раздел ТХ)',
        'УстрЗам_Прочее' => '📦 Устранения замечаний РК (Прочее оборудование)',
        // Supervision works activity types
        'ПроверкаМонтажнойПлощадки' => '🏗️ Проверка монтажной площадки',
        'ПодписаниеАктаШМР' => '📋 Подписание акта ШМР',
        'ПодтверждениеВехи_ШМР' => '✅ Этап шеф-монтажа завершен',
        // Supervision works milestone activities - standardized format matching commissioning works
        'ШМР_ВходнойКонтроль_ПодтВех' => '✅ Подтвердить достижение вехи (Входной контроль)',
        'ШМР_ЗавозОборудования_ПодтВех' => '✅ Подтвердить достижение вехи (Завоз оборудования на площадку)',
        'ШМР_РасстановкаБлоков_ПодтВех' => '✅ Подтвердить достижение вехи (Расстановка блоков)',
        'ШМР_МонтажПлощадок_ПодтВех' => '✅ Подтвердить достижение вехи (Монтаж площадок обслуживания)',
        'ШМР_МонтажЭлементовУкрытия_ПодтВех' => '✅ Подтвердить достижение вехи (Монтаж элементов укрытия)',
        'ШМР_СборкаКровли_ПодтВех' => '✅ Подтвердить достижение вехи (Сборка кровли)',
        'ШМР_МонтажТрубопроводов_ПодтВех' => '✅ Подтвердить достижение вехи (Монтаж трубопроводов)',
        'ШМР_ГидроПневмоИспытания_ПодтВех' => '✅ Подтвердить достижение вехи (Гидро/пневмо испытания)',
        'ШМР_МонтажТеплоизоляции_ПодтВех' => '✅ Подтвердить достижение вехи (Монтаж теплоизоляции)',
        'ШМР_МонтажВентиляции_ПодтВех' => '✅ Подтвердить достижение вехи (Монтаж вентиляции)',
        'ШМР_МонтажСнятогоОборудования_ПодтВех' => '✅ Подтвердить достижение вехи (Монтаж снятого оборудования)',
        'ШМР_МонтажКабельнойПродукции_ПодтВех' => '✅ Подтвердить достижение вехи (Монтаж кабельной продукции)',
        // Supervision works "Работы по графику" activities - standardized format
        'ШМР_ВходнойКонтроль_РабПоГр' => '📋 Работы по графику (Входной контроль)',
        'ШМР_ЗавозОборудования_РабПоГр' => '📋 Работы по графику (Завоз оборудования на площадку)',
        'ШМР_РасстановкаБлоков_РабПоГр' => '📋 Работы по графику (Расстановка блоков)',
        'ШМР_МонтажПлощадок_РабПоГр' => '📋 Работы по графику (Монтаж площадок обслуживания)',
        'ШМР_МонтажЭлементовУкрытия_РабПоГр' => '📋 Работы по графику (Монтаж элементов укрытия)',
        'ШМР_СборкаКровли_РабПоГр' => '📋 Работы по графику (Сборка кровли)',
        'ШМР_МонтажТрубопроводов_РабПоГр' => '📋 Работы по графику (Монтаж трубопроводов)',
        'ШМР_ГидроПневмоИспытания_РабПоГр' => '📋 Работы по графику (Гидро/пневмо испытания)',
        'ШМР_МонтажТеплоизоляции_РабПоГр' => '📋 Работы по графику (Монтаж теплоизоляции)',
        'ШМР_МонтажВентиляции_РабПоГр' => '📋 Работы по графику (Монтаж вентиляции)',
        'ШМР_МонтажСнятогоОборудования_РабПоГр' => '📋 Работы по графику (Монтаж снятого оборудования)',
        'ШМР_МонтажКабельнойПродукции_РабПоГр' => '📋 Работы по графику (Монтаж кабельной продукции)',
        // Supervision works "Работы по графику" activities - SHORT FORM mappings (for callback compatibility)
        'ШМР_ВхКонтр_РабПоГр' => '📅 Работы по графику (Входной контроль)',
        'ШМР_ЗавОбор_РабПоГр' => '📅 Работы по графику (Завоз оборудования на площадку)',
        'ШМР_РасстБлок_РабПоГр' => '📅 Работы по графику (Расстановка блоков)',
        'ШМР_МонтПлощ_РабПоГр' => '📅 Работы по графику (Монтаж площадок обслуживания)',
        'ШМР_МонтЭлУкр_РабПоГр' => '📅 Работы по графику (Монтаж элементов укрытия)',
        'ШМР_СборКровл_РабПоГр' => '📅 Работы по графику (Сборка кровли)',
        'ШМР_МонтТруб_РабПоГр' => '📅 Работы по графику (Монтаж трубопроводов)',
        'ШМР_ГидрИсп_РабПоГр' => '📅 Работы по графику (Гидро/пневмо испытания)',
        'ШМР_МонтТеплИз_РабПоГр' => '📅 Работы по графику (Монтаж теплоизоляции)',
        'ШМР_МонтВент_РабПоГр' => '📅 Работы по графику (Монтаж вентиляции)',
        'ШМР_МонтСнятОб_РабПоГр' => '📅 Работы по графику (Монтаж снятого оборудования)',
        'ШМР_МонтКаб_РабПоГр' => '📅 Работы по графику (Монтаж кабельной продукции)',
        // NEW: Additional mounting area activity mappings for backward compatibility
        'ШМР_ПроверкаМонтажнойПлощадки_РабПоГр' => '📅 Работы по графику (Проверка монтажной площадки)',
        // Supervision works "Подтвердить достижение вехи" activities - SHORT FORM mappings
        'ШМР_ВхКонтр_ПодтВех' => '✅ Этап шеф-монтажа завершен (Входной контроль)',
        'ШМР_ЗавОбор_ПодтВех' => '✅ Этап шеф-монтажа завершен (Завоз оборудования на площадку)',
        'ШМР_РасстБлок_ПодтВех' => '✅ Этап шеф-монтажа завершен (Расстановка блоков)',
        'ШМР_МонтПлощ_ПодтВех' => '✅ Этап шеф-монтажа завершен (Монтаж площадок обслуживания)',
        'ШМР_МонтЭлУкр_ПодтВех' => '✅ Этап шеф-монтажа завершен (Монтаж элементов укрытия)',
        'ШМР_СборКровл_ПодтВех' => '✅ Этап шеф-монтажа завершен (Сборка кровли)',
        'ШМР_МонтТруб_ПодтВех' => '✅ Этап шеф-монтажа завершен (Монтаж трубопроводов)',
        'ШМР_ГидрИсп_ПодтВех' => '✅ Этап шеф-монтажа завершен (Гидро/пневмо испытания)',
        'ШМР_МонтТеплИз_ПодтВех' => '✅ Этап шеф-монтажа завершен (Монтаж теплоизоляции)',
        'ШМР_МонтВент_ПодтВех' => '✅ Этап шеф-монтажа завершен (Монтаж вентиляции)',
        'ШМР_МонтСнятОб_ПодтВех' => '✅ Этап шеф-монтажа завершен (Монтаж снятого оборудования)',
        'ШМР_МонтКаб_ПодтВех' => '✅ Этап шеф-монтажа завершен (Монтаж кабельной продукции)',
        // Warranty works equipment types
        'РабГарантии_ЭлектрОб' => '⚡ Работы по гарантии (Электрооборудование)',
        'РабГарантии_СрИзм' => '📎 Работы по гарантии (Средства измерений)',
        'РабГарантии_НасОб' => '💧 Работы по гарантии (Насосное оборудование)',
        'РабГарантии_ТрубАрм' => '🔧 Работы по гарантии (Трубопроводная арматура)',
        'РабГарантии_ИспМех' => '⚙️ Работы по гарантии (Исполнительные механизмы)',
        'РабГарантии_КонтрОб' => '💻 Работы по гарантии (Контроллерное оборудование)',
        'РабГарантии_Прочее' => '📦 Работы по гарантии (Прочее оборудование)'
    ];

    private DatabaseService $databaseService;
    private MessageService $messageService;

    public function __construct(DatabaseService $databaseService, MessageService $messageService)
    {
        $this->databaseService = $databaseService;
        $this->messageService = $messageService;
    }

    /**
     * Check if user can add tabel entry (not added in last 10 minutes)
     * Admin users (level 3+) and super admins are exempt from this check
     * Level 1 users (Пользователь) are also exempt when explicitly confirming
     */
    public function canAddEntry(int $userId, bool $isSuperAdmin = false, int $userAccessLevel = 0): bool
    {
        // Admin users (level 3+) and super admins are exempt from this check
        // Level 1 users (Пользователь) are also exempt when explicitly confirming
        if ($userAccessLevel >= 3 || $userAccessLevel == 1 || $isSuperAdmin) {
            return true;
        }
        
        $recent = $this->databaseService->hasRecentTabelEntry($userId, $isSuperAdmin);
        return $recent === null;
    }

    /**
     * Get recent entry info for error message
     * Admin users (level 3+) and super admins are exempt from this check
     * Level 1 users (Пользователь) are also exempt when explicitly confirming
     */
    public function getRecentEntryInfo(int $userId, bool $isSuperAdmin = false, int $userAccessLevel = 0): ?array
    {
        // Admin users (level 3+) and super admins are exempt from this check
        // Level 1 users (Пользователь) are also exempt when explicitly confirming
        if ($userAccessLevel >= 3 || $userAccessLevel == 1 || $isSuperAdmin) {
            return null;
        }
        
        return $this->databaseService->hasRecentTabelEntry($userId, $isSuperAdmin);
    }

    /**
     * Create tabel entry message
     */
    public function createTabelMessage(string $fio, string $date, string $projectCode, string $activityType, string $idleReason = ''): string
    {
        // DEBUG: Log all parameters received
        error_log("🔍 TabelEntry::createTabelMessage called with:");
        error_log("  fio: '{$fio}'");
        error_log("  date: '{$date}'");
        error_log("  projectCode: '{$projectCode}'");
        error_log("  activityType: '{$activityType}'");
        error_log("  idleReason: '{$idleReason}'");
        
        $activityText = $this->activityTypeMappings[$activityType] ?? $activityType;
        error_log("  activityText from lookup: '{$activityText}'");
        
        // Для простоя добавляем детальную причину
        if (($activityType === 'Простой' || $activityType === 'ШМ_Простой') && !empty($idleReason)) {
            $activityText .= " ({$idleReason})";
            error_log("  Added idle reason, activityText now: '{$activityText}'");
        }
        
        // Для "Прочее" activities добавляем пользовательское описание
        if ($activityType === 'Прочее_ЭлектрОб' && !empty($idleReason)) {
            $activityText .= " ({$idleReason})";
            error_log("  Added custom description for Electrical Equipment Prochee, activityText now: '{$activityText}'");
        } else if ($activityType === 'Прочее_ЭлектрОб') {
            error_log("  Electrical Equipment Prochee activity detected but idleReason is empty!");
        }
        
        // Для Fire Alarm Equipment "Прочее" activities добавляем пользовательское описание
        if ($activityType === 'Прочее_ПожСигн' && !empty($idleReason)) {
            $activityText .= " ({$idleReason})";
            error_log("  Added custom description for Fire Alarm Equipment Prochee, activityText now: '{$activityText}'");
        } else if ($activityType === 'Прочее_ПожСигн') {
            error_log("  Fire Alarm Equipment Prochee activity detected but idleReason is empty!");
        }
        
        // Для Security Alarm Equipment "Прочее" activities добавляем пользовательское описание
        if ($activityType === 'Прочее_ОхрСигн' && !empty($idleReason)) {
            $activityText .= " ({$idleReason})";
            error_log("  Added custom description for Security Alarm Equipment Prochee, activityText now: '{$activityText}'");
        } else if ($activityType === 'Прочее_ОхрСигн') {
            error_log("  Security Alarm Equipment Prochee activity detected but idleReason is empty!");
        }
        
        // Для Automation Equipment "Прочее" activities добавляем пользовательское описание
        if ($activityType === 'Прочее_Автом' && !empty($idleReason)) {
            $activityText .= " ({$idleReason})";
            error_log("  Added custom description for Automation Equipment Prochee, activityText now: '{$activityText}'");
        } else if ($activityType === 'Прочее_Автом') {
            error_log("  Automation Equipment Prochee activity detected but idleReason is empty!");
        }
        
        // Для Heating and Ventilation Equipment "Прочее" activities добавляем пользовательское описание
        if ($activityType === 'Прочее_ВентОтопл' && !empty($idleReason)) {
            $activityText .= " ({$idleReason})";
            error_log("  Added custom description for Heating and Ventilation Equipment Prochee, activityText now: '{$activityText}'");
        } else if ($activityType === 'Прочее_ВентОтопл') {
            error_log("  Heating and Ventilation Equipment Prochee activity detected but idleReason is empty!");
        }
        
        // Для Technological Equipment "Прочее" activities добавляем пользовательское описание
        if ($activityType === 'Прочее_ТехОб' && !empty($idleReason)) {
            $activityText .= " ({$idleReason})";
            error_log("  Added custom description for Technological Equipment Prochee, activityText now: '{$activityText}'");
        } else if ($activityType === 'Прочее_ТехОб') {
            error_log("  Technological Equipment Prochee activity detected but idleReason is empty!");
        }
        
        // Для Metrology Equipment "Прочее" activities добавляем пользовательское описание
        if ($activityType === 'Проч_Метрол' && !empty($idleReason)) {
            $activityText .= " ({$idleReason})";
            error_log("  Added custom description for Metrology Equipment Prochee, activityText now: '{$activityText}'");
        } else if ($activityType === 'Проч_Метрол') {
            error_log("  Metrology Equipment Prochee activity detected but idleReason is empty!");
        }
        
        // Для Project Activity "Прочее" activities добавляем пользовательское описание
        if ($activityType === 'Прочее_ПроектнаяДеятельность' && !empty($idleReason)) {
            $activityText .= " ({$idleReason})";
            error_log("  Added custom description for Project Activity Prochee, activityText now: '{$activityText}'");
        } else if ($activityType === 'Прочее_ПроектнаяДеятельность') {
            error_log("  Project Activity Prochee activity detected but idleReason is empty!");
        }
        
        // Для Supervision Project Activity "Прочее" activities добавляем пользовательское описание
        if ($activityType === 'Прочее_ПроектнаяДеятельность_ШМР' && !empty($idleReason)) {
            $activityText .= " ({$idleReason})";
            error_log("  Added custom description for Supervision Project Activity Prochee, activityText now: '{$activityText}'");
        } else if ($activityType === 'Прочее_ПроектнаяДеятельность_ШМР') {
            error_log("  Supervision Project Activity Prochee activity detected but idleReason is empty!");
        }
        
        // Для RK Comments Elimination "Прочее" activities добавляем пользовательское описание
        if ($activityType === 'УстрЗам_Прочее') {
            error_log("TabelEntry: Processing RK Comments Elimination Prochee activity");
            error_log("TabelEntry: idleReason received: '{$idleReason}'");
            error_log("TabelEntry: activityText before custom text: '{$activityText}'");
            
            if (!empty($idleReason)) {
                $activityText .= " ({$idleReason})";
                error_log("  Added custom description for RK Comments Elimination Prochee, activityText now: '{$activityText}'");
            } else {
                error_log("  RK Comments Elimination Prochee activity detected but idleReason is empty!");
            }
        }
        
        // Для Warranty Equipment "Прочее" activities добавляем пользовательское описание
        if ($activityType === 'РабГарантии_Прочее') {
            error_log("TabelEntry: Processing Warranty Equipment Prochee activity");
            error_log("TabelEntry: idleReason received: '{$idleReason}'");
            error_log("TabelEntry: activityText before custom text: '{$activityText}'");
            
            if (!empty($idleReason)) {
                $activityText .= " ({$idleReason})";
                error_log("  Added custom description for Warranty Equipment Prochee, activityText now: '{$activityText}'");
            } else {
                error_log("  Warranty Equipment Prochee activity detected but idleReason is empty!");
            }
        }
        
        // Commissioning activities ("Отдел ПНР") - use new format: "Богданов Борис Сергеевич ✅19.092025, Проект ОИ834: Добавлена запись в табель: Комплексное опробование"
        $commissioningActivities = [
            'КомплексноеОпробование', 'РежимнаяНаладка', 'ПодготовкаКПоездке', 'СдачаАвансовогоОтчета',
            'Инструктаж', 'Дорога', 'ОхраннаяСигнализация', 'ОтоплениеИВентиляция', 'ТехнологическоеОборудование', 'Метрология',
            // Supervision works sub-activities
            'ШМР_РаботыПоГрафику', 'ШМР_ПодготовкаПоездки', 'ШМР_Дорога', 'ШМР_Инструктаж', 'ШМР_СдачаОтчета',
            // Supervision works milestone confirmation
            'ПодтверждениеВехи_ШМР', 'ПроверкаМонтажнойПлощадки', 'ПодписаниеАктаШМР'
        ];
        
        if (in_array($activityType, $commissioningActivities)) {
            $result = sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
            
            return $result;
        }
        
        // Special handling for milestone completion (ПодтВех_ЭлектрОб)
        if ($activityType === 'ПодтВех_ЭлектрОб') {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Электрооборудование)</b>",
                $fio,
                $date,
                $projectCode
            );
        }
        
        // Special handling for milestone completion (ПодтВех_ПожСигн)
        if ($activityType === 'ПодтВех_ПожСигн') {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Пожарная сигнализация)</b>",
                $fio,
                $date,
                $projectCode
            );
        }
        
        // Special handling for milestone completion (ПодтВех_ОхрСигн)
        if ($activityType === 'ПодтВех_ОхрСигн') {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Охранная сигнализация)</b>",
                $fio,
                $date,
                $projectCode
            );
        }
        
        // Special handling for milestone completion (ПодтВех_Автом)
        if ($activityType === 'ПодтВех_Автом') {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Автоматизация)</b>",
                $fio,
                $date,
                $projectCode
            );
        }
        
        // Special handling for milestone completion (ПодтВех_ВентОтопл)
        if ($activityType === 'ПодтВех_ВентОтопл') {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Вентиляция и отопление)</b>",
                $fio,
                $date,
                $projectCode
            );
        }
        
        // Special handling for milestone completion (ПодтВех_ТехОб)
        if ($activityType === 'ПодтВех_ТехОб') {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Технологическое оборудование)</b>",
                $fio,
                $date,
                $projectCode
            );
        }
        
        // Special handling for milestone completion (ПодтВех_Метрол)
        if ($activityType === 'ПодтВех_Метрол') {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Метрология)</b>",
                $fio,
                $date,
                $projectCode
            );
        }
        
        // Project activities ("Проектная деятельность") - use new format
        $projectActivities = [
            'УчастиеВЗПИ', 'РазрПрограммы', 'РазрГрафика', 'ОргДопоставки', 'УчастиеВОПИ', 'ИспОбМаст', 'ШефНаладочныеРаботы', 'Прочее_ПроектнаяДеятельность', 'Прочее_ПроектнаяДеятельность_ШМР'
        ];
        
        if (in_array($activityType, $projectActivities)) {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
        }
        
        // Electrical equipment activities ("Электрооборудование") - use new format
        $electricalEquipmentActivities = [
            'РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб',
            'ИзмСхПд_ЭлектрОб', 'МонтКбЛн_ЭлектрОб', 'РаскКбЛн_ЭлектрОб', 'МонтОб_ЭлектрОб', 'Прочее_ЭлектрОб'
        ];
        
        if (in_array($activityType, $electricalEquipmentActivities)) {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
        }
        
        // Fire alarm equipment activities ("Пожарная сигнализация") - use new format
        $fireAlarmEquipmentActivities = [
            'РабПоГр_ПожСигн', 'ФормИД_ПожСигн', 'ПодтВех_ПожСигн',
            'ИзмСх_ПожСигн', 'МонтКб_ПожСигн', 'РаскКб_ПожСигн', 'МонтОб_ПожСигн', 'Прочее_ПожСигн'
        ];
        
        if (in_array($activityType, $fireAlarmEquipmentActivities)) {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
        }
        
        // Security alarm equipment activities ("Охранная сигнализация") - use new format
        $securityAlarmEquipmentActivities = [
            'РабПоГр_ОхрСигн', 'ФормИД_ОхрСигн', 'ПодтВех_ОхрСигн',
            'ИзмСх_ОхрСигн', 'МонтКб_ОхрСигн', 'РаскКб_ОхрСигн', 'МонтОб_ОхрСигн', 'Прочее_ОхрСигн'
        ];
        
        if (in_array($activityType, $securityAlarmEquipmentActivities)) {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
        }
        
        // Heating and ventilation equipment activities ("Вентиляция и отопление") - use new format
        $heatingVentilationEquipmentActivities = [
            'РабПоГр_ВентОтопл', 'ФормИД_ВентОтопл', 'ПодтВех_ВентОтопл',
            'ДопРаб_ВентОтопл', 'ИзмСх_ВентОтопл', 'МонтКб_ВентОтопл', 'РаскКб_ВентОтопл', 
            'МонтАСУ_ВентОтопл', 'УдалПО_ВентОтопл', 'Прочее_ВентОтопл'
        ];
        
        if (in_array($activityType, $heatingVentilationEquipmentActivities)) {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
        }
        
        // Automation equipment activities ("Автоматизация") - use new format
        $automationEquipmentActivities = [
            'РабПоГр_Автом', 'ФормИД_Автом', 'ПодтВех_Автом',
            'ДопРаб_Автом', 'ИзмСх_Автом', 'МонтКб_Автом', 'РаскКб_Автом', 
            'МонтАСУ_Автом', 'УдалПО_Автом', 'Прочее_Автом'
        ];
        
        if (in_array($activityType, $automationEquipmentActivities)) {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
        }
        
        // Technological equipment activities ("Технологическое оборудование") - use new format
        $technologicalEquipmentActivities = [
            'РабПоГр_ТехОб', 'ФормИД_ТехОб', 'ПодтВех_ТехОб', 'ДопРаб_ТехОб',
            'ДемАрм_ТехОб', 'ДемОб_ТехОб', 'Прочее_ТехОб'
        ];
        
        if (in_array($activityType, $technologicalEquipmentActivities)) {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
        }
        
        // Metrology equipment activities ("Метрология") - use new format
        $metrologyEquipmentActivities = [
            'РабПоГр_Метрол', 'ФормИД_Метрол', 'ПодтВех_Метрол', 'ДопРаб_Метрол',
            'ИзмКонф_Метрол', 'КалПов_Метрол', 'КМХ_Метрол', 'КоррХар_Метрол', 'Проч_Метрол'
        ];
        
        if (in_array($activityType, $metrologyEquipmentActivities)) {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
        }
        
        // Supervision works installation activities ("ШМР - Монтажные работы") - SHORT FORM names (matching callbacks)
        $supervisionInstallationActivities = [
            'ШМР_ВхКонтр_РабПоГр', 'ШМР_ВхКонтр_ПодтВех',
            'ШМР_ЗавОбор_РабПоГр', 'ШМР_ЗавОбор_ПодтВех',
            'ШМР_РасстБлок_РабПоГр', 'ШМР_РасстБлок_ПодтВех',
            'ШМР_МонтПлощ_РабПоГр', 'ШМР_МонтПлощ_ПодтВех',
            'ШМР_МонтЭлУкр_РабПоГр', 'ШМР_МонтЭлУкр_ПодтВех',
            'ШМР_СборКровл_РабПоГр', 'ШМР_СборКровл_ПодтВех',
            'ШМР_МонтТруб_РабПоГр', 'ШМР_МонтТруб_ПодтВех',
            'ШМР_ГидрИсп_РабПоГр', 'ШМР_ГидрИсп_ПодтВех',
            'ШМР_МонтТеплИз_РабПоГр', 'ШМР_МонтТеплИз_ПодтВех',
            'ШМР_МонтВент_РабПоГр', 'ШМР_МонтВент_ПодтВех',
            'ШМР_МонтСнятОб_РабПоГр', 'ШМР_МонтСнятОб_ПодтВех',
            'ШМР_МонтКаб_РабПоГр', 'ШМР_МонтКаб_ПодтВех'
        ];
        
        if (in_array($activityType, $supervisionInstallationActivities)) {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
        }
        
        // RK comments elimination activities ("Устранения замечаний РК") - use new format (same as electrical equipment)
        $rkCommentsEliminationActivities = [
            'УстрЗам_ЭС', 'УстрЗам_АТХ', 'УстрЗам_ОВКВ', 'УстрЗам_ПСОС', 'УстрЗам_ТХ', 'УстрЗам_Прочее'
        ];
        
        if (in_array($activityType, $rkCommentsEliminationActivities)) {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
        }
        
        // Warranty equipment activities ("Работы по гарантии") - use new format like Automation
        $warrantyEquipmentActivities = [
            'РабГарантии_ЭлектрОб', 'РабГарантии_СрИзм', 'РабГарантии_НасОб',
            'РабГарантии_ТрубАрм', 'РабГарантии_ИспМех', 'РабГарантии_КонтрОб', 'РабГарантии_Прочее'
        ];
        
        if (in_array($activityType, $warrantyEquipmentActivities)) {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
        }
        
        // Idle activities ("Простой") - use new commissioning format
        $idleActivities = ['Простой', 'ШМ_Простой'];
        
        if (in_array($activityType, $idleActivities)) {
            return sprintf(
                "<b>%s</b>\r\n😨 ⚠️ %s, Проект <b>%s</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>⚠️ %s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
        }
        
        // Для текущей деятельности используем формат БЕЗ проекта (Current Activities не относятся к конкретным проектам)
        // TKP Business Directions (ПодготовкаТКП_*) используют формат С проектом как у Автоматизации
        if (in_array($activityType, ['МедОсмотр', 'Аттестация', 'ПодготовкаТКП', 'Общая']) || $projectCode === 'Текущая деятельность') {
            return sprintf(
                "<b>%s</b>\r\n✅Запись в табель от <b>%s</b>: <b>%s</b> добавлена",
                $fio,
                $date,
                $activityText
            );
        }
        
        // TKP Business Directions используют формат как у Автоматизации (эталон)
        if (in_array($activityType, ['ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_МСО', 'ПодготовкаТКП_АСН'])) {
            return sprintf(
                "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
                $fio,
                $date,
                $projectCode,
                $activityText
            );
        }
        
        return sprintf(
            "<b>%s</b>\r\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о <b>%s добавлена</b>",
            $fio,
            $date,
            $projectCode,
            $activityText
        );
    }

    /**
     * Add tabel entry and send notifications
     */
    public function addEntry(int $userId, string $fio, string $date, string $projectCode, string $activityType, string $chatId, string $idleReason = ''): bool
    {
        error_log("=== 🔧 WARRANTY DEBUG: TabelEntry->addEntry CALLED ===");
        error_log("🔧 WARRANTY DEBUG: addEntry called with userId: {$userId}, activityType: '{$activityType}', projectCode: '{$projectCode}'");
        error_log("🔧 WARRANTY DEBUG: fio: '{$fio}', date: '{$date}', idleReason: '{$idleReason}'");
        
        error_log("🔧 WARRANTY DEBUG: About to call createTabelMessage");
        $message = $this->createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason);
        error_log("🔧 WARRANTY DEBUG: createTabelMessage returned: " . substr($message, 0, 100) . "...");
        
        // Save to database
        error_log("🔧 WARRANTY DEBUG: About to save to database");
        $success = $this->databaseService->addUserTabel($userId, $message);
        
        error_log("🔧 WARRANTY DEBUG: Database save result: " . ($success ? 'SUCCESS' : 'FAILED'));
        error_log("🔧 WARRANTY DEBUG: Generated message: {$message}");
        
        if ($success) {
            error_log("🔧 WARRANTY DEBUG: Database save successful, proceeding with history chat sending");
            // Получаем функциональное направление пользователя
            $userData = \pDB::getUserData($userId);
            $functionalDirection = $userData['functional_direction'] ?? 'Мобильная бригада';
            
            error_log("🔧 WARRANTY DEBUG: User functional direction: '{$functionalDirection}'");
            
            // Отправляем в соответствующую тему на основе функционального направления
            error_log("🔧 WARRANTY DEBUG: About to send to history chat");
            $this->messageService->sendToHistoryChatByFunctionalDirection($message, $functionalDirection);
            error_log("🔧 WARRANTY DEBUG: History chat send completed");
            
            // Проверяем, есть ли проекты на контроле для этого пользователя и данного проекта
            error_log("🔧 WARRANTY DEBUG: About to send controlled project copies");
            $this->sendControlledProjectCopies($userId, $projectCode, $message);
            error_log("🔧 WARRANTY DEBUG: Controlled project copies completed");
        } else {
            error_log("🔧 WARRANTY DEBUG: Database save FAILED - this is the root cause!");
        }
        
        error_log("🔧 WARRANTY DEBUG: addEntry returning: " . ($success ? 'true' : 'false'));
        return $success;
    }
    
    /**
     * Send copies of reports to controlled project settings
     */
    private function sendControlledProjectCopies(int $userId, string $projectCode, string $message): void
    {
        try {
            // Получаем всех пользователей из базы данных
            $allUsers = $this->databaseService->getAllUsers();
            
            foreach ($allUsers as $user) {
                $supervisorUserId = $user['user_id'];
                $userData = $user['data'];
                
                // Пропускаем самого пользователя, который создал отчет
                if ($supervisorUserId == $userId) {
                    continue;
                }
                
                // Проверяем, есть ли у этого пользователя проекты на контроле
                $controlProjects = $userData['control_projects'] ?? [];
                
                // Если у пользователя есть данный проект на контроле
                if (isset($controlProjects[$projectCode])) {
                    $projectSettings = $controlProjects[$projectCode];
                    
                    // ИСПРАВЛЕНИЕ: Проверяем на дублирование по отношению к ФН СУПЕРВАЙЗЕРА, а не отправителя
                    $supervisorFunctionalDirection = $userData['functional_direction'] ?? 'Мобильная бригада';
                    $supervisorGroupAndThread = $this->getSenderGroupAndThread($supervisorFunctionalDirection);
                    
                    $isDuplicate = $this->isDuplicateDestination(
                        $projectSettings, 
                        $supervisorGroupAndThread
                    );
                    
                    if ($isDuplicate) {
                        error_log("TabelEntry: Skipping duplicate for {$projectCode} to supervisor {$supervisorUserId} - same destination as supervisor's functional direction ({$supervisorFunctionalDirection})");
                        continue;
                    }
                    
                    // Отправляем копию в настроенную группу с правильной обработкой chat_id как message_thread_id
                    if (!empty($projectSettings['group_id'])) {
                        try {
                            // Подготовка параметров отправки (такая же логика как в тестовом уведомлении)
                            $data = [
                                'chat_id' => $projectSettings['group_id'],
                                'text' => $message,
                                'parse_mode' => 'HTML'
                            ];
                            
                            // Добавляем message_thread_id если указан chat_id (как в системе отчетов)
                            if (!empty($projectSettings['chat_id']) && $projectSettings['chat_id'] !== 'personal') {
                                $data['message_thread_id'] = $projectSettings['chat_id'];
                            }
                            
                            \Longman\TelegramBot\Request::sendMessage($data);
                            
                            error_log("TabelEntry: Sent controlled project copy for {$projectCode} to supervisor {$supervisorUserId} group {$projectSettings['group_id']}" . 
                                     (!empty($projectSettings['chat_id']) && $projectSettings['chat_id'] !== 'personal' ? " (thread {$projectSettings['chat_id']})" : ""));
                            
                        } catch (\Exception $e) {
                            error_log("TabelEntry: Error sending controlled project copy for {$projectCode} to supervisor {$supervisorUserId}: " . $e->getMessage());
                        }
                    }
                    
                    // Отправляем в личный чат если указан personal
                    if (!empty($projectSettings['chat_id']) && $projectSettings['chat_id'] === 'personal') {
                        try {
                            $this->messageService->sendMessage($supervisorUserId, $message);
                            error_log("TabelEntry: Sent controlled project copy for {$projectCode} to personal chat of supervisor {$supervisorUserId}");
                        } catch (\Exception $e) {
                            error_log("TabelEntry: Error sending controlled project copy to personal chat for {$projectCode} to supervisor {$supervisorUserId}: " . $e->getMessage());
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("TabelEntry: Error in sendControlledProjectCopies: " . $e->getMessage());
        }
    }

    /**
     * Get group and thread for sender's functional direction
     */
    private function getSenderGroupAndThread(string $functionalDirection): array
    {
        switch ($functionalDirection) {
            case 'Мобильная бригада':
                return [
                    'group_id' => '-1002722176147',
                    'thread_id' => '2'
                ];
            case 'Пусконаладка':
                return [
                    'group_id' => '-1001400593888',
                    'thread_id' => '338'
                ];
            case 'Шеф-монтаж':
                return [
                    'group_id' => '-1001865074576',
                    'thread_id' => '14627'
                ];
            default:
                return [
                    'group_id' => '-1002722176147',
                    'thread_id' => '2'
                ];
        }
    }

    /**
     * Check if controlled project settings would result in duplicate message
     */
    private function isDuplicateDestination(array $projectSettings, array $senderGroupAndThread): bool
    {
        // Если group_id не совпадает, то не дубликат
        if (empty($projectSettings['group_id']) || $projectSettings['group_id'] !== $senderGroupAndThread['group_id']) {
            return false;
        }
        
        // Если group_id совпадает, проверяем thread_id
        $projectThreadId = $projectSettings['chat_id'] ?? null;
        
        // КЛЮЧЕВОЕ ИСПРАВЛЕНИЕ: если group_id совпадает, но chat_id пустой или 'personal',
        // сообщение все равно попадет в ту же группу (в основную ленту)
        // Это ТОЖЕ дублирование, так как пользователи видят сообщение в группе дважды:
        // 1. В конкретной теме (от функционального направления)
        // 2. В основной ленте группы (от контролируемого проекта)
        if (empty($projectThreadId) || $projectThreadId === 'personal') {
            // Если в той же группе, но без thread_id - это дублирование!
            return true;
        }
        
        // Проверяем, совпадает ли thread_id
        return $projectThreadId === $senderGroupAndThread['thread_id'];
    }

    /**
     * Get confirmation message for tabel entry
     */
    public function getConfirmationMessage(string $activityType, string $projectCode): string
    {
        $activityNames = [
            // Standard activity types
            'Дорога' => 'Дорога',
            'Подготовка' => 'Подготовка к служебной поездке',
            'Инструктаж' => 'Инструктаж',
            'Ожидание' => 'Ожидание',
            'Гарантия' => 'Гарантия',
            'Простой' => 'Простой',
            'ШМ_Простой' => 'Простой (Шеф-монтаж)',
            // TKP preparation
            'ПодготовкаТКП_ОПНВГ' => 'Подготовка ТКП (ОПНВГ)',
            'ПодготовкаТКП_НКО' => 'Подготовка ТКП (НКО)',
            'ПодготовкаТКП_МСО' => 'Подготовка ТКП (МСО)',
            'ПодготовкаТКП_АСН' => 'Подготовка ТКП (АСН)',
            // Commissioning
            'ПНР' => 'Пусконаладочные работы',
            'УЗ_РК' => 'Устранение замечаний РК',
            'РабГарантии' => 'Работы по гарантии',
            // Commissioning activities ("Отдел ПНР")
            'КомплексноеОпробование' => 'Комплексное опробование',
            'РежимнаяНаладка' => 'Режимная наладка',
            'ПодготовкаКПоездке' => 'Подготовка к служебной поездке',
            'СдачаАвансовогоОтчета' => 'Сдача авансового отчета',
            'ОхраннаяСигнализация' => 'Охранная сигнализация',
            'ОтоплениеИВентиляция' => 'Отопление и вентиляция',
            'ТехнологическоеОборудование' => 'Технологическое оборудование',
            'Метрология' => 'Метрология',
            // Supervision
            'ШМР' => 'Шеф-монтажные работы',
            'ШМ_УЗ_РК' => 'Устранение замечаний РК (Шеф-монтаж)',
            'ШМ_РабГарантии' => 'Работы по гарантии (Шеф-монтаж)',
            'ШМ_Простой' => 'Простой (Шеф-монтаж)',
            // Supervision works sub-activities
            'ШМР_РаботыПоГрафику' => 'Работы по графику',
            'ШМР_ПодготовкаПоездки' => 'Подготовка к служебной поездке',
            'ШМР_Дорога' => 'Дорога',
            'ШМР_Инструктаж' => 'Инструктаж',
            'ШМР_СдачаОтчета' => 'Сдача авансового отчета',
            // Current activities
            'МедОсмотр' => 'Медицинский осмотр',
            'Аттестация' => 'Аттестация',
            'ПодготовкаТКП' => 'Подготовка ТКП',
            'Общая' => 'Общая деятельность',
            // New current activity types
            'УчастиеВЗПИ' => 'Участие в ЗПИ',
            'РазрПрограммы' => 'Разработка программы работ',
            'РазрГрафика' => 'Разработка графика работ',
            'ОргДопоставки' => 'Организация допоставки',
            'УчастиеВОПИ' => 'Участие в ОПИ',
            'ШефНаладочныеРаботы' => 'Шеф-наладочные работы',
            'РазработкаПрограммыРабот' => 'Разработка программы работ',
            'РазработкаГрафикаРабот' => 'Разработка графика работ',
            'ПодготовкаОтчета' => 'Подготовка отчета',
            // Supervision project activity types
            'ИспОбМаст' => 'Исполнение обязанностей мастера',
            // Mobile brigade
            'Электромонтажные' => 'Электромонтажные работы',
            'Сварочные' => 'Сварочные работы',
            'Слесарные' => 'Слесарные работы',
            'Теплоизоляция' => 'Теплоизоляция',
            'Документы' => 'Отчётные документы',
            'МедОсмотр' => 'МедОсмотр',
            'Аттестация' => 'Аттестация',
            'ПодготовкаТКП' => 'ПодготовкаТКП',
            'Общая' => 'Общая деятельность',
            'ПодготовкаТКП_ОПНВГ' => 'Подготовка материала к ТКП, БН ОПНВГ',
            'ПодготовкаТКП_НКО' => 'Подготовка материала к ТКП, БН НКО',
            'ПодготовкаТКП_МСО' => 'Подготовка материала к ТКП, БН МСО',
            'ПодготовкаТКП_АСН' => 'Подготовка материала к ТКП, БН АСН',
            // Новые типы для Пусконаладки (сокращенные названия)
            'ПНР' => 'Пусконаладочные работы',
            'УЗ_РК' => 'Устранение замечаний РК',
            'РабГарантии' => 'Работы по гарантии',
            'Простой' => 'Простой',
            // Новые типы для Шеф-монтажа (сокращенные названия)
            'ШМР' => 'Шеф-монтажные работы',
            'ШМ_УЗ_РК' => 'Устранение замечаний РК (Шеф-монтаж)',
            'ШМ_РабГарантии' => 'Работы по гарантии (Шеф-монтаж)',
            'ШМ_Простой' => 'Простой (Шеф-монтаж)',
            // New electrical equipment activity types
            'РабПоГр_ЭлектрОб' => 'Работы по графику (Электрооборудование)',
            'ФормИД_ЭлектрОб' => 'Формирование ИД (Электрооборудование)',
            'ПодтВех_ЭлектрОб' => 'Подтвердить завершения этапа наладки (Электрооборудование)',
            'ПодтВех_ПожСигн' => 'Подтвердить завершения этапа наладки (Пожарная сигнализация)',
            'ИзмСхПд_ЭлектрОб' => 'Изменение схемы подключения (Электрооборудование, Дополнительные работы)',
            'МонтКбЛн_ЭлектрОб' => 'Прокладка кабеля (Электрооборудование, Дополнительные работы)',
            'РаскКбЛн_ЭлектрОб' => 'Подключение кабеля (Электрооборудование, Дополнительные работы)',
            'МонтОб_ЭлектрОб' => 'Монтаж оборудования (Электрооборудование, Дополнительные работы)',
            'Прочее_ЭлектрОб' => 'Прочее (Электрооборудование, Дополнительные работы)',
            // Electrical equipment main category
            'Электрооборудование' => 'Электрооборудование',
            // Automation equipment activity types
            'РабПоГр_Автом' => 'Работы по графику (Автоматизация)',
            'ФормИД_Автом' => 'Формирование ИД (Автоматизация)',
            'ПодтВех_Автом' => 'Подтвердить достижение вехи (Автоматизация)',
            'ДопРаб_Автом' => 'Дополнительные работы (Автоматизация)',
            'ИзмСх_Автом' => 'Изменение схемы подключения (Автоматизация, Дополнительные работы)',
            'МонтКб_Автом' => 'Прокладка кабеля (Автоматизация, Дополнительные работы)',
            'РаскКб_Автом' => 'Подключение кабеля (Автоматизация, Дополнительные работы)',
            'МонтАСУ_Автом' => 'Монтаж оборудования АСУ ТП (Автоматизация, Дополнительные работы)',
            'УдалПО_Автом' => 'Удаленный ПНР ПО (Автоматизация, Дополнительные работы)',
            'Прочее_Автом' => 'Прочее (Автоматизация, Дополнительные работы)',
            // Fire alarm equipment activity types
            'РабПоГр_ПожСигн' => 'Работы по графику (Пожарная сигнализация)',
            'ФормИД_ПожСигн' => 'Формирование ИД (Пожарная сигнализация)',
            'ПодтВех_ПожСигн' => 'Подтвердить достижение вехи (Пожарная сигнализация)',
            'ДопРаб_ПожСигн' => 'Дополнительные работы (Пожарная сигнализация)',
            'ИзмСх_ПожСигн' => 'Изменение схемы подключения (Пожарная сигнализация, Дополнительные работы)',
            'МонтКб_ПожСигн' => 'Прокладка кабеля (Пожарная сигнализация, Дополнительные работы)',
            'РаскКб_ПожСигн' => 'Подключение кабеля (Пожарная сигнализация, Дополнительные работы)',
            'МонтОб_ПожСигн' => 'Монтаж оборудования (Пожарная сигнализация, Дополнительные работы)',
            'Прочее_ПожСигн' => 'Прочее (Пожарная сигнализация, Дополнительные работы)',
            // Security alarm equipment activity types
            'РабПоГр_ОхрСигн' => 'Работы по графику (Охранная сигнализация)',
            'ФормИД_ОхрСигн' => 'Формирование ИД (Охранная сигнализация)',
            'ПодтВех_ОхрСигн' => 'Подтвердить достижение вехи (Охранная сигнализация)',
            'ИзмСх_ОхрСигн' => 'Изменение схемы подключения (Охранная сигнализация, Дополнительные работы)',
            'МонтКб_ОхрСигн' => 'Прокладка кабеля (Охранная сигнализация, Дополнительные работы)',
            'РаскКб_ОхрСигн' => 'Подключение кабеля (Охранная сигнализация, Дополнительные работы)',
            'МонтОб_ОхрСигн' => 'Монтаж оборудования (Охранная сигнализация, Дополнительные работы)',
            'Прочее_ОхрСигн' => 'Прочее (Охранная сигнализация, Дополнительные работы)',
            // Heating and ventilation equipment activity types
            'РабПоГр_ВентОтопл' => 'Работы по графику (Вентиляция и отопление)',
            'ФормИД_ВентОтопл' => 'Формирование ИД (Вентиляция и отопление)',
            'ПодтВех_ВентОтопл' => 'Подтвердить достижение вехи (Вентиляция и отопление)',
            'ДопРаб_ВентОтопл' => 'Дополнительные работы (Вентиляция и отопление)',
            'ИзмСх_ВентОтопл' => 'Изменение схемы подключения (Вентиляция и отопление, Дополнительные работы)',
            'МонтКб_ВентОтопл' => 'Прокладка кабеля (Вентиляция и отопление, Дополнительные работы)',
            'РаскКб_ВентОтопл' => 'Подключение кабеля (Вентиляция и отопление, Дополнительные работы)',
            'МонтАСУ_ВентОтопл' => 'Монтаж оборудования АСУ ТП (Вентиляция и отопление, Дополнительные работы)',
            'УдалПО_ВентОтопл' => 'Удаленный ПНР ПО (Вентиляция и отопление, Дополнительные работы)',
            'Прочее_ВентОтопл' => 'Прочее (Вентиляция и отопление, Дополнительные работы)',
            // Technological equipment activity types
            'РабПоГр_ТехОб' => 'Работы по графику (Технологическое оборудование)',
            'ФормИД_ТехОб' => 'Формирование ИД (Технологическое оборудование)',
            'ПодтВех_ТехОб' => 'Подтвердить достижение вехи (Технологическое оборудование)',
            'ДопРаб_ТехОб' => 'Дополнительные работы (Технологическое оборудование)',
            'ДемАрм_ТехОб' => 'Демонтаж/ревизия арматуры (Технологическое оборудование, Дополнительные работы)',
            'ДемОб_ТехОб' => 'Демонтаж/ревизия техн. оборудования (Технологическое оборудование, Дополнительные работы)',
            'Прочее_ТехОб' => 'Прочее (Технологическое оборудование, Дополнительные работы)',
            // Metrology activity types
            'РабПоГр_Метрол' => 'Работы по графику (Метрология)',
            'ФормИД_Метрол' => 'Формирование ИД (Метрология)',
            'ПодтВех_Метрол' => 'Подтвердить достижение вехи (Метрология)',
            'ДопРаб_Метрол' => 'Дополнительные работы (Метрология)',
            'ИзмКонф_Метрол' => 'Изменение конфигурации СИ (Метрология, Дополнительные работы)',
            'КалПов_Метрол' => 'Калибровка/поверка оборудования (Метрология, Дополнительные работы)',
            'КМХ_Метрол' => 'КМХ СИ (Метрология, Дополнительные работы)',
            'КоррХар_Метрол' => 'Коррекция характеристик СИ (Метрология, Дополнительные работы)',
            'Проч_Метрол' => 'Прочее (Метрология, Дополнительные работы)',
            // RK comments elimination activity types
            'УстрЗам_ЭС' => 'Устранения замечаний РК (Раздел ЭС)',
            'УстрЗам_АТХ' => 'Устранения замечаний РК (Раздел АТХ)',
            'УстрЗам_ОВКВ' => 'Устранения замечаний РК (Раздел ОВКВ)',
            'УстрЗам_ПСОС' => 'Устранения замечаний РК (Раздел ПС/ОС)',
            'УстрЗам_ТХ' => 'Устранения замечаний РК (Раздел ТХ)',
            'УстрЗам_Прочее' => 'Устранения замечаний РК (Прочее оборудование)',
            // Warranty works equipment types
            'РабГарантии_ЭлектрОб' => 'Работы по гарантии (Электрооборудование)',
            'РабГарантии_СрИзм' => 'Работы по гарантии (Средства измерений)',
            'РабГарантии_НасОб' => 'Работы по гарантии (Насосное оборудование)',
            'РабГарантии_ТрубАрм' => 'Работы по гарантии (Трубопроводная арматура)',
            'РабГарантии_ИспМех' => 'Работы по гарантии (Исполнительные механизмы)',
            'РабГарантии_КонтрОб' => 'Работы по гарантии (Контроллерное оборудование)',
            'РабГарантии_Прочее' => 'Работы по гарантии (Прочее оборудование)',
            // Project Activity "Prochee" type for confirmation
            'Прочее_ПроектнаяДеятельность' => 'Прочее (Проектная деятельность)',
            // Supervision Project Activity "Prochee" type for confirmation
            'Прочее_ПроектнаяДеятельность_ШМР' => 'Прочее (Проектная деятельность)',
            // Simple supervision works activities
            'ПроверкаМонтажнойПлощадки' => 'Проверка монтажной площадки',
            'ПодписаниеАктаШМР' => 'Подписание акта ШМР',
            // Supervision works milestone activities for confirmation messages
            'ШМР_ВходнойКонтроль_ПодтВех' => 'Подтвердить достижение вехи (Входной контроль)',
            'ШМР_ЗавозОборудования_ПодтВех' => 'Подтвердить достижение вехи (Завоз оборудования на площадку)',
            'ШМР_РасстановкаБлоков_ПодтВех' => 'Подтвердить достижение вехи (Расстановка блоков)',
            'ШМР_МонтажПлощадок_ПодтВех' => 'Подтвердить достижение вехи (Монтаж площадок обслуживания)',
            'ШМР_МонтажЭлементовУкрытия_ПодтВех' => 'Подтвердить достижение вехи (Монтаж элементов укрытия)',
            'ШМР_СборкаКровли_ПодтВех' => 'Подтвердить достижение вехи (Сборка кровли)',
            'ШМР_МонтажТрубопроводов_ПодтВех' => 'Подтвердить достижение вехи (Монтаж трубопроводов)',
            'ШМР_ГидроПневмоИспытания_ПодтВех' => 'Подтвердить достижение вехи (Гидро/пневмо испытания)',
            'ШМР_МонтажТеплоизоляции_ПодтВех' => 'Подтвердить достижение вехи (Монтаж теплоизоляции)',
            'ШМР_МонтажВентиляции_ПодтВех' => 'Подтвердить достижение вехи (Монтаж вентиляции)',
            'ШМР_МонтажСнятогоОборудования_ПодтВех' => 'Подтвердить достижение вехи (Монтаж снятого оборудования)',
            'ШМР_МонтажКабельнойПродукции_ПодтВех' => 'Подтвердить достижение вехи (Монтаж кабельной продукции)',
            // Supervision works "Работы по графику" activities for confirmation messages
            'ШМР_ВходнойКонтроль_РабПоГр' => 'Работы по графику (Входной контроль)',
            'ШМР_ЗавозОборудования_РабПоГр' => 'Работы по графику (Завоз оборудования на площадку)',
            'ШМР_РасстановкаБлоков_РабПоГр' => 'Работы по графику (Расстановка блоков)',
            'ШМР_МонтажПлощадок_РабПоГр' => 'Работы по графику (Монтаж площадок обслуживания)',
            'ШМР_МонтажЭлементовУкрытия_РабПоГр' => 'Работы по графику (Монтаж элементов укрытия)',
            'ШМР_СборкаКровли_РабПоГр' => 'Работы по графику (Сборка кровли)',
            'ШМР_МонтажТрубопроводов_РабПоГр' => 'Работы по графику (Монтаж трубопроводов)',
            'ШМР_ГидроПневмоИспытания_РабПоГр' => 'Работы по графику (Гидро/пневмо испытания)',
            'ШМР_МонтажТеплоизоляции_РабПоГр' => 'Работы по графику (Монтаж теплоизоляции)',
            'ШМР_МонтажВентиляции_РабПоГр' => 'Работы по графику (Монтаж вентиляции)',
            'ШМР_МонтажСнятогоОборудования_РабПоГр' => 'Работы по графику (Монтаж снятого оборудования)',
            'ШМР_МонтажКабельнойПродукции_РабПоГр' => 'Работы по графику (Монтаж кабельной продукции)'
        ];

        $activityName = $activityNames[$activityType] ?? $activityType;
        
        // Special handling for milestone completion (ПодтВех_ЭлектрОб)
        if ($activityType === 'ПодтВех_ЭлектрОб') {
            return sprintf(
                "Проект <b>%s</b>: Подтвердить завершения этапа наладки (Электрооборудование). Отправить запись в табель?",
                $projectCode
            );
        }
        
        // Special handling for milestone completion (ПодтВех_ПожСигн)
        if ($activityType === 'ПодтВех_ПожСигн') {
            return sprintf(
                "Проект <b>%s</b>: Подтвердить завершения этапа наладки (Пожарная сигнализация). Отправить запись в табель?",
                $projectCode
            );
        }
        
        // Special handling for milestone completion (ПодтВех_ОхрСигн)
        if ($activityType === 'ПодтВех_ОхрСигн') {
            return sprintf(
                "Проект <b>%s</b>: Подтвердить завершения этапа наладки (Охранная сигнализация). Отправить запись в табель?",
                $projectCode
            );
        }
        
        // Special handling for milestone completion (ПодтВех_Автом)
        if ($activityType === 'ПодтВех_Автом') {
            return sprintf(
                "Проект <b>%s</b>: Подтвердить завершения этапа наладки (Автоматизация). Отправить запись в табель?",
                $projectCode
            );
        }
        
        // Special handling for milestone completion (ПодтВех_ВентОтопл)
        if ($activityType === 'ПодтВех_ВентОтопл') {
            return sprintf(
                "Проект <b>%s</b>: Подтвердить завершения этапа наладки (Вентиляция и отопление). Отправить запись в табель?",
                $projectCode
            );
        }
        
        // Special handling for milestone completion (ПодтВех_ТехОб)
        if ($activityType === 'ПодтВех_ТехОб') {
            return sprintf(
                "Проект <b>%s</b>: Подтвердить завершения этапа наладки (Технологическое оборудование). Отправить запись в табель?",
                $projectCode
            );
        }
        
        // Special handling for milestone completion (ПодтВех_Метрол)
        if ($activityType === 'ПодтВех_Метрол') {
            return sprintf(
                "Проект <b>%s</b>: Подтвердить завершения этапа наладки (Метрология). Отправить запись в табель?",
                $projectCode
            );
        }
        
        // Special handling for supervision works schedule activities (SHORT FORM)
        if (str_contains($activityType, 'ШМР_') && str_contains($activityType, '_РабПоГр')) {
            // DEBUG: Log the exact activity type received
            error_log("🔍 TabelEntry::getConfirmationMessage - ШМР schedule activity type: '{$activityType}'");
            
            // Extract work area name from short form activity type
            $workAreaMap = [
                'ШМР_ВхКонтр_РабПоГр' => 'Входной контроль',
                'ШМР_ЗавОбор_РабПоГр' => 'Завоз оборудования на площадку',
                'ШМР_РасстБлок_РабПоГр' => 'Расстановка блоков',
                'ШМР_МонтПлощ_РабПоГр' => 'Монтаж площадок обслуживания',
                'ШМР_МонтЭлУкр_РабПоГр' => 'Монтаж элементов укрытия',
                'ШМР_СборКровл_РабПоГр' => 'Сборка кровли',
                'ШМР_МонтТруб_РабПоГр' => 'Монтаж трубопроводов',
                'ШМР_ГидрИсп_РабПоГр' => 'Гидро/пневмо испытания',
                'ШМР_МонтТеплИз_РабПоГр' => 'Монтаж теплоизоляции',
                'ШМР_МонтВент_РабПоГр' => 'Монтаж вентиляции',
                'ШМР_МонтСнятОб_РабПоГр' => 'Монтаж снятого оборудования',
                'ШМР_МонтКаб_РабПоГр' => 'Монтаж кабельной продукции',
                // FULL FORM mappings (for backward compatibility)
                'ШМР_ВходнойКонтроль_РабПоГр' => 'Входной контроль',
                'ШМР_ЗавозОборудования_РабПоГр' => 'Завоз оборудования на площадку',
                'ШМР_РасстановкаБлоков_РабПоГр' => 'Расстановка блоков',
                'ШМР_МонтажПлощадок_РабПоГр' => 'Монтаж площадок обслуживания',
                'ШМР_МонтажЭлементовУкрытия_РабПоГр' => 'Монтаж элементов укрытия',
                'ШМР_СборкаКровли_РабПоГр' => 'Сборка кровли',
                'ШМР_МонтажТрубопроводов_РабПоГр' => 'Монтаж трубопроводов',
                'ШМР_ГидроПневмоИспытания_РабПоГр' => 'Гидро/пневмо испытания',
                'ШМР_МонтажТеплоизоляции_РабПоГр' => 'Монтаж теплоизоляции',
                'ШМР_МонтажВентиляции_РабПоГр' => 'Монтаж вентиляции',
                'ШМР_МонтажСнятогоОборудования_РабПоГр' => 'Монтаж снятого оборудования',
                'ШМР_МонтажКабельнойПродукции_РабПоГр' => 'Монтаж кабельной продукции'
            ];
            
            $workArea = $workAreaMap[$activityType] ?? 'Неизвестный этап';
            error_log("🔍 TabelEntry::getConfirmationMessage - Work area mapped to: '{$workArea}'");
            
            return sprintf(
                "Проект <b>%s</b>: <b>Работы по графику (%s)</b>. Отправить запись в табель?",
                $projectCode,
                $workArea
            );
        }
        
        // Special handling for supervision works milestone completion activities (SHORT FORM)
        if (str_contains($activityType, 'ШМР_') && str_contains($activityType, '_ПодтВех')) {
            // DEBUG: Log the exact activity type received
            error_log("🔍 TabelEntry::getConfirmationMessage - ШМР milestone activity type: '{$activityType}'");
            
            // Extract work area name from short form activity type
            $workAreaMap = [
                'ШМР_ВхКонтр_ПодтВех' => 'Входной контроль',
                'ШМР_ЗавОбор_ПодтВех' => 'Завоз оборудования на площадку',
                'ШМР_РасстБлок_ПодтВех' => 'Расстановка блоков',
                'ШМР_МонтПлощ_ПодтВех' => 'Монтаж площадок обслуживания',
                'ШМР_МонтЭлУкр_ПодтВех' => 'Монтаж элементов укрытия',
                'ШМР_СборКровл_ПодтВех' => 'Сборка кровли',
                'ШМР_МонтТруб_ПодтВех' => 'Монтаж трубопроводов',
                'ШМР_ГидрИсп_ПодтВех' => 'Гидро/пневмо испытания',
                'ШМР_МонтТеплИз_ПодтВех' => 'Монтаж теплоизоляции',
                'ШМР_МонтВент_ПодтВех' => 'Монтаж вентиляции',
                'ШМР_МонтСнятОб_ПодтВех' => 'Монтаж снятого оборудования',
                'ШМР_МонтКаб_ПодтВех' => 'Монтаж кабельной продукции',
                // FULL FORM mappings (for backward compatibility)
                'ШМР_ВходнойКонтроль_ПодтВех' => 'Входной контроль',
                'ШМР_ЗавозОборудования_ПодтВех' => 'Завоз оборудования на площадку',
                'ШМР_РасстановкаБлоков_ПодтВех' => 'Расстановка блоков',
                'ШМР_МонтажПлощадок_ПодтВех' => 'Монтаж площадок обслуживания',
                'ШМР_МонтажЭлементовУкрытия_ПодтВех' => 'Монтаж элементов укрытия',
                'ШМР_СборкаКровли_ПодтВех' => 'Сборка кровли',
                'ШМР_МонтажТрубопроводов_ПодтВех' => 'Монтаж трубопроводов',
                'ШМР_ГидроПневмоИспытания_ПодтВех' => 'Гидро/пневмо испытания',
                'ШМР_МонтажТеплоизоляции_ПодтВех' => 'Монтаж теплоизоляции',
                'ШМР_МонтажВентиляции_ПодтВех' => 'Монтаж вентиляции',
                'ШМР_МонтажСнятогоОборудования_ПодтВех' => 'Монтаж снятого оборудования',
                'ШМР_МонтажКабельнойПродукции_ПодтВех' => 'Монтаж кабельной продукции'
            ];
            
            $workArea = $workAreaMap[$activityType] ?? 'Неизвестный этап';
            error_log("🔍 TabelEntry::getConfirmationMessage - Work area mapped to: '{$workArea}'");
            
            return sprintf(
                "Проект <b>%s</b>: Подтвердить завершение этапа шеф-монтажа (%s). Отправить запись в табель?",
                $projectCode,
                $workArea
            );
        }
        
        // Special handling for supervision works milestone completion activities (FULL FORM - legacy support)
        if (str_contains($activityType, 'ШМР_') && str_contains($activityType, '_ПодтВех')) {
            return sprintf(
                "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
                $projectCode,
                $activityName
            );
        }
        
        // Idle activities (should include both regular and supervision modes)  
        $idleActivities = ['Простой', 'ШМ_Простой'];
        
        if (in_array($activityType, $idleActivities)) {
            // Special format for idle activities - include stage information
            $stageInfo = str_contains($activityType, 'ШМ_') ? 'Шеф-монтаж' : 'Пусконаладочные работы';
            
            // For commissioning works, check if we have a specialized idle reason
            $idleReasonText = '';
            if ($stageInfo === 'Пусконаладочные работы' && !empty($idleReason)) {
                // Apply commissioning-specific formatting to idle reason
                $formattedIdleReason = $this->formatCommissioningIdleReason($idleReason);
                $idleReasonText = "\n\n<b>Причина простоя:</b> {$formattedIdleReason}";
            } elseif (!empty($idleReason)) {
                $idleReasonText = "\n\n<b>Причина простоя:</b> {$idleReason}";
            }
            
            return sprintf(
                "😨 Проект <b>%s</b>: <b>⚠️ Простой (Инцидент)</b>. Этап: <b>%s</b>. Отправить запись в табель?%s",
                $projectCode,
                $stageInfo,
                $idleReasonText
            );
        }
        
        // Commissioning activities ("Отдел ПНР") - use new format: "Проект ОИ834: Комплексное опробование. Отправить запись в табель?"
        $commissioningActivities = [
            'КомплексноеОпробование', 'РежимнаяНаладка', 'ПодготовкаКПоездке', 'СдачаАвансовогоОтчета',
            'Инструктаж', 'Дорога', 'ОхраннаяСигнализация', 'ОтоплениеИВентиляция', 'ТехнологическоеОборудование', 'Метрология',
            // Supervision works sub-activities
            'ШМР_РаботыПоГрафику', 'ШМР_ПодготовкаПоездки', 'ШМР_Дорога', 'ШМР_Инструктаж', 'ШМР_СдачаОтчета',
            // Simple supervision works activities
            'ПроверкаМонтажнойПлощадки', 'ПодписаниеАктаШМР'
        ];
        
        // Project activities ("Проектная деятельность") - use new format
        $projectActivities = [
            'УчастиеВЗПИ', 'РазрПрограммы', 'РазрГрафика', 'ОргДопоставки', 'УчастиеВОПИ', 'ИспОбМаст', 'ШефНаладочныеРаботы'
        ];
        
        // Electrical equipment activities ("Электрооборудование") - use new format
        $electricalEquipmentActivities = [
            'РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб',
            'ИзмСхПд_ЭлектрОб', 'МонтКбЛн_ЭлектрОб', 'РаскКбЛн_ЭлектрОб', 'МонтОб_ЭлектрОб', 'Прочее_ЭлектрОб'
        ];
        
        if (in_array($activityType, $commissioningActivities) || in_array($activityType, $projectActivities)) {
            return sprintf(
                "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
                $projectCode,
                $activityName
            );
        }
        
        // Fire alarm equipment activities ("Пожарная сигнализация") - use new format
        $fireAlarmEquipmentActivities = [
            'РабПоГр_ПожСигн', 'ФормИД_ПожСигн', 'ПодтВех_ПожСигн',
            'ИзмСх_ПожСигн', 'МонтКб_ПожСигн', 'РаскКб_ПожСигн', 'МонтОб_ПожСигн', 'Прочее_ПожСигн'
        ];
        
        // Security alarm equipment activities ("Охранная сигнализация") - use new format
        $securityAlarmEquipmentActivities = [
            'РабПоГр_ОхрСигн', 'ФормИД_ОхрСигн', 'ПодтВех_ОхрСигн',
            'ИзмСх_ОхрСигн', 'МонтКб_ОхрСигн', 'РаскКб_ОхрСигн', 'МонтОб_ОхрСигн', 'Прочее_ОхрСигн'
        ];
        
        if (in_array($activityType, $electricalEquipmentActivities)) {
            return sprintf(
                "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
                $projectCode,
                $activityName
            );
        }
        
        // Automation equipment activities ("Автоматизация") - use new format
        $automationEquipmentActivities = [
            'РабПоГр_Автом', 'ФормИД_Автом', 'ПодтВех_Автом',
            'ДопРаб_Автом', 'ИзмСх_Автом', 'МонтКб_Автом', 'РаскКб_Автом', 
            'МонтАСУ_Автом', 'УдалПО_Автом', 'Прочее_Автом'
        ];
        
        if (in_array($activityType, $automationEquipmentActivities)) {
            return sprintf(
                "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
                $projectCode,
                $activityName
            );
        }
        
        if (in_array($activityType, $fireAlarmEquipmentActivities)) {
            return sprintf(
                "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
                $projectCode,
                $activityName
            );
        }
        
        if (in_array($activityType, $securityAlarmEquipmentActivities)) {
            return sprintf(
                "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
                $projectCode,
                $activityName
            );
        }
        
        // Heating and ventilation equipment activities ("Вентиляция и отопление") - use new format
        $heatingVentilationEquipmentActivities = [
            'РабПоГр_ВентОтопл', 'ФормИД_ВентОтопл', 'ПодтВех_ВентОтопл',
            'ДопРаб_ВентОтопл', 'ИзмСх_ВентОтопл', 'МонтКб_ВентОтопл', 'РаскКб_ВентОтопл', 
            'МонтАСУ_ВентОтопл', 'УдалПО_ВентОтопл', 'Прочее_ВентОтопл'
        ];
        
        if (in_array($activityType, $heatingVentilationEquipmentActivities)) {
            return sprintf(
                "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
                $projectCode,
                $activityName
            );
        }
        
        // Technological equipment activities ("Технологическое оборудование") - use new format
        $technologicalEquipmentActivities = [
            'РабПоГр_ТехОб', 'ФормИД_ТехОб', 'ПодтВех_ТехОб',
            'ДопРаб_ТехОб', 'ДемАрм_ТехОб', 'ДемОб_ТехОб', 'Прочее_ТехОб'
        ];
        
        if (in_array($activityType, $technologicalEquipmentActivities)) {
            return sprintf(
                "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
                $projectCode,
                $activityName
            );
        }
        
        // Metrology activities ("Метрология") - use new format
        $metrologyActivities = [
            'РабПоГр_Метрол', 'ФормИД_Метрол', 'ПодтВех_Метрол', 'ДопРаб_Метрол',
            'ИзмКонф_Метрол', 'КалПов_Метрол', 'КМХ_Метрол', 'КоррХар_Метрол', 'Проч_Метрол'
        ];
        
        if (in_array($activityType, $metrologyActivities)) {
            return sprintf(
                "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
                $projectCode,
                $activityName
            );
        }
        
        // Supervision works milestone activities ("Подтвердить достижение вехи") - use new standardized format
        $supervisionMilestoneActivities = [
            'ШМР_ВходнойКонтроль_ПодтВех', 'ШМР_ЗавозОборудования_ПодтВех',
            'ШМР_РасстановкаБлоков_ПодтВех', 'ШМР_МонтажПлощадок_ПодтВех',
            'ШМР_МонтажЭлементовУкрытия_ПодтВех', 'ШМР_СборкаКровли_ПодтВех',
            'ШМР_МонтажТрубопроводов_ПодтВех', 'ШМР_ГидроПневмоИспытания_ПодтВех',
            'ШМР_МонтажТеплоизоляции_ПодтВех', 'ШМР_МонтажВентиляции_ПодтВех',
            'ШМР_МонтажСнятогоОборудования_ПодтВех', 'ШМР_МонтажКабельнойПродукции_ПодтВех'
        ];
        
        // Supervision works "schedule" activities ("Работы по графику") - use new standardized format
        $supervisionScheduleActivities = [
            'ШМР_ВходнойКонтроль_РабПоГр', 'ШМР_ЗавозОборудования_РабПоГр',
            'ШМР_РасстановкаБлоков_РабПоГр', 'ШМР_МонтажПлощадок_РабПоГр',
            'ШМР_МонтажЭлементовУкрытия_РабПоГр', 'ШМР_СборкаКровли_РабПоГр',
            'ШМР_МонтажТрубопроводов_РабПоГр', 'ШМР_ГидроПневмоИспытания_РабПоГр',
            'ШМР_МонтажТеплоизоляции_РабПоГр', 'ШМР_МонтажВентиляции_РабПоГр',
            'ШМР_МонтажСнятогоОборудования_РабПоГр', 'ШМР_МонтажКабельнойПродукции_РабПоГр'
        ];
        
        if (in_array($activityType, $supervisionMilestoneActivities) || in_array($activityType, $supervisionScheduleActivities)) {
            return sprintf(
                "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
                $projectCode,
                $activityName
            );
        }
        
        // RK comments elimination activities ("Устранения замечаний РК") - use new format
        $rkCommentsActivities = [
            'УстрЗам_ЭС', 'УстрЗам_АТХ', 'УстрЗам_ОВКВ', 'УстрЗам_ПСОС', 'УстрЗам_ТХ', 'УстрЗам_Прочее'
        ];
        
        if (in_array($activityType, $rkCommentsActivities)) {
            return sprintf(
                "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
                $projectCode,
                $activityName
            );
        }
        
        // Warranty equipment activities ("Работы по гарантии") - use Automation format
        $warrantyEquipmentActivities = [
            'РабГарантии_ЭлектрОб', 'РабГарантии_СрИзм', 'РабГарантии_НасОб',
            'РабГарантии_ТрубАрм', 'РабГарантии_ИспМех', 'РабГарантии_КонтрОб', 'РабГарантии_Прочее'
        ];
        
        if (in_array($activityType, $warrantyEquipmentActivities)) {
            return sprintf(
                "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
                $projectCode,
                $activityName
            );
        }
        
        // Для текущей деятельности используем формат БЕЗ проекта (Current Activities не относятся к конкретным проектам)
        // TKP Business Directions (ПодготовкаТКП_*) используют формат С проектом как у Автоматизации
        if (in_array($activityType, ['МедОсмотр', 'Аттестация', 'ПодготовкаТКП', 'Общая']) || $projectCode === 'Текущая деятельность') {
            // Use full descriptive names from activityTypeMappings for Current Activities
            $fullActivityName = $this->activityTypeMappings[$activityType] ?? $activityName;
            return sprintf(
                "<b>%s</b>. Отправить запись в табель?",
                $fullActivityName
            );
        }
        
        // TKP Business Directions используют формат как у Автоматизации (эталон)
        if (in_array($activityType, ['ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_МСО', 'ПодготовкаТКП_АСН'])) {
            return sprintf(
                "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
                $projectCode,
                $activityName
            );
        }
        
        return sprintf(
            "<b>%s</b>, отправить запись в табель в проект <b>%s</b>?",
            $activityName,
            $projectCode
        );
    }

    /**
     * Format recent entry error message
     */
    public function formatRecentEntryError(array $recentEntry): string
    {
        $formattedTime = date("H:i:s d.m.Y", strtotime($recentEntry["dt"]) + 3600 * 2);
        
        return sprintf(
            '❌ За последние <b>10 минут</b> вы уже добавляли запись в табель [<b>%s</b>]',
            $formattedTime
        );
    }

    /**
     * Format idle reason for commissioning works with Parent (Subcategory) format
     */
    private function formatCommissioningIdleReason(string $idleReason): string
    {
        // If the idle reason is just 'Простой', try to enhance it
        if ($idleReason === 'Простой') {
            return 'Простой'; // Return as-is for now
        }
        
        // Apply commissioning-specific formatting rules
        // This method can be enhanced to parse and format idle reasons
        // For now, return the idle reason as provided
        return $idleReason;
    }
}