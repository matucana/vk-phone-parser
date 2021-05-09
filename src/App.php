<?php


namespace Matucana\VkPhoneParser;


use Generator;
use VK\Client\VKApiClient;
use League\Csv\Writer;

class App
{
    private VKApiClient $vkApiClient;

    private UsersUidProvider $usersUidProvider;

    public function __construct(VKApiClient $vkApiClient, UsersUidProvider $usersUidProvider)
    {
        $this->vkApiClient = $vkApiClient;
        $this->usersUidProvider = $usersUidProvider;
    }

    public function run(): Generator
    {
        $writer = Writer::createFromPath(__DIR__ . '/../file.csv', 'w+');
        $writer->insertOne(['ID Вконтакте', 'Имя', 'Фамилия', 'Номер телефона']);
        $all_search = 0;

        foreach ($this->usersUidProvider->getSeriesUid() as $user_ids) {
            $params = [
                'user_ids' => $user_ids,
                'fields' => 'contacts'
            ];

            $result = $this->vkApiClient->users()->get($_ENV['VK_SERVICE_KEY'], $params);
            $data = $this->validator($this->parser($result));
            $writer->insertAll($this->clearArray($data));

            if ($all_search === 0) {
                yield 'Запускаем сканирование' . PHP_EOL . 'Сканируем ' . $this->usersUidProvider->getLimit(
                    ) . ' пользователей' . PHP_EOL;
            }

            $all_search = $all_search + count($data);

            yield 'Обработано ' . ($this->usersUidProvider->getStart() + 999) . ' => Найдено номеров: ' . count(
                    $data
                ) . ' | Всего найдено: ' . $all_search . ' | Осталось обработать ' . ($this->usersUidProvider->getLimit(
                    ) - ($this->usersUidProvider->getStart() + 999));
        }
        yield 'Сканирование завершено!' . PHP_EOL . 'Обработано: ' . $this->usersUidProvider->getLimit(
            ) . ' пользователей' . PHP_EOL . 'Найдено номеров: ' . $all_search;
    }

    public function parser(array $result): array
    {
        return array_filter(
            $result,
            function ($v) {
                return isset($v["mobile_phone"]);
            }
        );
    }

    public function phoneValidator(string $phone): bool
    {
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $swissNumberProto = $phoneUtil->parse($phone, "RU");
            if ($phoneUtil->isValidNumber($swissNumberProto)) {
                return true;
            }
            return false;
        } catch (\libphonenumber\NumberParseException $e) {
            return false;
        }
    }

    public function validator(array $result): array
    {
        return array_filter(
            $result,
            function ($v) {
                return ($this->phoneValidator($v["mobile_phone"]) === true);
            }
        );
    }

    public function clearArray(array $result): array
    {
        $sort = [];
        foreach ($result as $item => $value) {
            $sort[$item] = [
                'id' => 'https://vk.com/id' . $value['id'],
                'first_name' => $value['first_name'],
                'last_name' => $value['last_name'],
                'mobile_phone' => $value['mobile_phone']
            ];
        }
        return $sort;
    }


}