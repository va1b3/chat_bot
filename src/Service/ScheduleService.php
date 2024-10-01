<?php

use App\Service\AbstractService;

class ScheduleService extends AbstractService {
   
    private string $date_schedule;
    private string $date_from;
    private string $date_to;
    private array $schedule;
    private array $days = [ 
        'позавчера' => '-2 days',
        'послезавтра' => '+2 days',
        'вчера' => 'yesterday',
        'завтра' => 'tomorrow',
        'сегодня' => 'today',
        'понедельник' => 'monday',
        'вторник' => 'tuesday',
        'среда' => 'wednesday',
        'четверг' => 'thursday',
        'пятница' => 'friday',
        'суббота' => 'saturday',
        'воскресенье' => 'sunday'];

    #[\Override]
    public function response(): void {
        $matches = [];
        if (preg_match('/([0-9][0-9].[0-9][0-9]){1}/', $this->message, $matches)) {
            $this->parseDateNumbers($matches);
        } else {
            $this->parseDateText();
        }
        if($this->getSchedule()) {
            $messageID = $this->sendMessage($this->responseText());
            pinSchedule($messageID);
        } else {
            $this->sendPhoto('https://http.cat/500');
        }
    }

    #[\Override]
    public function test(): string {
        $matches = [];
        if (preg_match('/([0-9][0-9].[0-9][0-9]){1}/', $this->message, $matches)) {
            $this->parseDateNumbers($matches);
        } else {
            $this->parseDateText();
        }
        return $this->getSchedule() ? $this->responseText() : 'https://http.cat/500';
    }
    
    private function parseDateNumbers(array $matches): void {
        $date = preg_split('/[.]/', $matches[1]);
        $this->date_from = date('Y').'-'.$date[1].'-'.$date[0];
        $this->date_to = date('Y').'-'.$date[1].'-'.($date[0] + 1);
        $this->date_schedule = $date[0].'.'.$date[1].'.'.date('Y');
    }
    
    private function parseDateText(): void {
        $i = 0;
        $shift = '';
        foreach ($this->days as $name => $value) {
            $i++;
            if (preg_match('/'.$name.'/ui', $this->message)) {
                $date = $value;
                break;
            }
	}
        /*
         * Weeks shifting
         * E.g. next Wednesday | previous Friday
         * Only for days of the week
         */
        if ($i > 5) {
            if (preg_match('/след/ui', $this->message)) {
                $shift = 'next ';
            } elseif (preg_match('/пред/ui', $this->message)) {
                $shift = 'previous ';
            }
	}
        $date = $date ?? $this->days['сегодня'];
	$this->date_from = date('Y-m-d', strtotime($shift.$date));
	$this->date_to = date('Y-m-d', strtotime('+1 day', strtotime($shift.$date)));
	$this->date_schedule = date('d.m.Y', strtotime($shift.$date));
    }
    
    private function getSchedule(): bool {
        $curl = curl_init('https://univer.dvfu.ru/schedule/get?type=agendaWeek&start=' . 
                $this->date_from . 'T00%3A00%3A00&end=' . 
                $this->date_to . 'T00%3A00%3A00&groups%5B%5D=' . $_ENV['FEFU_GROUP_ID']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, 
                ['Cookie: '. $_ENV['FEFU_TOKEN'], 'X-Requested-With: XMLHttpRequest']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $this->schedule = json_decode(curl_exec($curl), true);
        curl_close($curl);
        return isset($this->schedule['events']);
    }
    
    private function responseText(): string {
        $response = '';
        if (count($this->schedule['events']) == 0) {
            $response = 'Расписание отсутствует' . PHP_EOL . PHP_EOL;
        } else {
            array_multisort(array_column($this->schedule['events'], 'order'),
                $this->schedule['events'], SORT_ASC);
            foreach ($this->schedule['events'] as $event) {
                $start = date('H:i', strtotime($event['start']));
                $end = date('H:i', strtotime($event['end']));
                $title = $event['title'];
                $type = $event['pps_load'] ? $event['pps_load'] : $event['control_type'];
                $subgroup = $event['subgroup'] ? '('.$event['subgroup'].')' : '';
                $classroom = $event['classroom'] ? ' — '.$event['classroom'] : '';
                $teacher = $event['teacher'] ? PHP_EOL .$event['teacher'] : '';
                $response .= $start . ' - ' . $end . ' | '. $title . ' (' . $type . ')' . 
                        $subgroup . $classroom . $teacher . PHP_EOL . PHP_EOL;
            }
        }
        $response .= '——— ' . $this->date_schedule . ' ———';
        return $response;
    }
    
    private function pinSchedule(string|int $messageID): void {
        if (preg_match('/закреп/ui', $this->message)) {
            $this->unpinAllChatMessages();
            $this->pinChatMessage($messageID);
        }
    }
}
