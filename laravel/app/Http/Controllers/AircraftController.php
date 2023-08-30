<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AircraftController extends Controller
{
    public function aircraft_airports(Request $request)
    {
        $params = $request->all();
        $a = DB::select('select
        f1.airport_id2 as airport_id, --аэропорт в котором приземлился самолет в строке 1 из полетов
        p.code_iata,p.code_icao, -- поля из этого аэропорта
        f1.cargo_offload , -- масса разгрузки в аэропорту из строки 1 полета
        f2.cargo_load , -- масса погурзки в аэропорту из строки 2 полета
        f1.landing , -- дата/время приземленя в аэропорт из строки 1 полета
        f2.takeoff  -- дата/время вылета из аэропорта из строки 2 полета
        from flights f1 -- примем как 1-ю строку полета, как ту, в которой самолет приземлился
        left join
        (select * from flights ff order by ff.takeoff asc) -- выберем взлеты, отсортированные по дате
        f2 on ( -- примем как 2-ю строку полета, как ту в которой самолет взлетел
            f1.airport_id2 = f2.airport_id1 --при том чтобы аэропорт взлета f2 совподал с аэропортом посадки f1
            and f2.takeoff>f1.landing -- время посадки должно быть больше времени взлета
        )
        left join airports p on f1.airport_id2=p.id -- приклеим данные об аэропорте
        left join aircrafts c on f1.aircraft_id=c.id -- приклеим данные о самолете
        where c.tail = ? -- tail из запроса
        and f1.landing >= ? -- date_from из запроса
        and f2.takeoff <= ? -- date_to из запроса',
         [$params['tail'],$params['date_from'],$params['date_to']]
        );
        return response()->json($a, 200,['Content-Type' => 'application/json;charset=UTF-8',
            'Charset' => 'utf-8'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
}
