<?php

namespace App\Http\Controllers;

use Log;
use Auth;
use App\User;
use DateTime;
use Exception;
use Carbon\Carbon;
use Box\Spout\Common\Type;
use Illuminate\Http\Request;
use Rkesa\Client\Models\Client;
use Rkesa\Service\Models\Service;
use Box\Spout\Reader\ReaderFactory;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Service\Models\ServiceState;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Service\Models\ServicePriority;
use Rkesa\Client\Models\ClientContactEmail;
use Rkesa\Client\Models\ClientContactPhone;
use Rkesa\Estimate\Models\EstimateLineData;
use Rkesa\Estimate\Models\EstimateLineCategory;


class ImportController extends Controller
{
    //    Не импортировать поле
    //    Создать новое поле
    //    Сделка
    //        Название сделки
    //        Бюджет сделки
    //        Ответственный за сделку
    //        Дата создания
    //        Кем создана сделка
    //        Тег сделки
    //        Примечание к сделке
    //        Статус сделки
    //        Воронка
    //        Дата закрытия
    //    Контакт
    //        Имя
    //        Отчество
    //        Фамилия
    //        Полное имя
    //        Ответственный за контакт
    //        Примечание к контакту
    //        Тег контакта
    //        Кем создан контакт
    //        Дата создания
    //        Рабочий телефон
    //        Рабочий прямой телефон
    //        Мобильный телефон
    //        Факс
    //        Домашний телефон
    //        Другой телефон
    //        Рабочий email
    //        Личный email
    //        Другой email
    //        Должность
    //    Компания
    //        Название (компания)
    //        Ответственный за компанию
    //        Дата создания
    //        Кем создана компания
    //        Примечание к компании
    //        Тег компании
    //        Рабочий телефон
    //        Рабочий прямой телефон
    //        Мобильный телефон
    //        Факс
    //        Домашний телефон
    //        Другой телефон
    //        Рабочий email
    //        Личный email
    //        Другой email
    //        Web
    //        Адрес

    private function get_reader_with_filename($filename)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $reader = null;
        switch ($ext) {
            case 'xls':
            case 'xlsx':
                $reader = ReaderFactory::create(Type::XLSX);
                break;
            case 'ods':
                $reader = ReaderFactory::create(Type::ODS);
                break;
            case 'csv':
                $reader = ReaderFactory::create(Type::CSV);
                $reader->setFieldDelimiter(';');
                break;
        }

        $reader->open(public_path($filename));

        return $reader;
    }

    public function import_settings(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $reader = $this->get_reader_with_filename($request['src']);

            $columns = [];
            $rows = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $index => $row) {
                    switch ($index) {
                        case 1: // column names
                            $columns = $row;
                            break;
                        case 2:
                        case 3:
                            $rows[] = $row;
                            break;
                        case 4:
                            break 3;
                    }
                }
                break;
            }

            $reader->close();

            $res->columns = $columns;
            $res->rows = $rows;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function import_data(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $current_user = Auth::user();

            $reader = $this->get_reader_with_filename($request['src']);

            $vals = $request['values'];

            $contact_exist = false;
            foreach ([1, 2, 3, 4] as $type) {
                if (in_array($type, $vals)) {
                    $contact_exist = true;
                }
            }

            $company_exist = false;
            foreach ([5, 6, 7, 8] as $type) {
                if (in_array($type, $vals)) {
                    $company_exist = true;
                }
            }

            $service_exist = false;
            foreach ([9, 10, 11, 12, 13, 14] as $type) {
                if (in_array($type, $vals)) {
                    $service_exist = true;
                }
            }

            foreach ($reader->getSheetIterator() as $s_index => $sheet) {
                foreach ($sheet->getRowIterator() as $r_index => $row) {
                    if ($r_index == 1) {
                        continue;
                    }
                    if ($company_exist) {
                        $name_i = array_search(5, $vals); // name
                        $phone_i = array_search(6, $vals); // phone
                        $email_i = array_search(7, $vals); // email
                        $company = Client::where('name', $row[$name_i])
                            ->where('phone', $row[$phone_i])
                            ->where('email', $row[$email_i])->first();
                        if ($company == null) {
                            $company = new Client;
                            $company->a_attributes = array();

                            foreach ($vals as $i => $type) {
                                switch ($type) {
                                    case 5:
                                        $company->name = $row[$i];
                                        break;
                                    case 6:
                                        $company->phone = $row[$i];
                                        break;
                                    case 7:
                                        $company->email = $row[$i];
                                        break;
                                    case 8:
                                        $company->note = $row[$i];
                                        break;
                                }
                            }
                            if ($company->name == null) {
                                $company->name = trans('template.Unknown');
                            }

                            $company->save();
                        }
                    }
                    if ($contact_exist) {
                        $name_i = array_search(1, $vals); // name
                        $phone_i = array_search(2, $vals); // phone
                        $phone = $row[$phone_i];
                        $email_i = array_search(3, $vals); // email
                        $email = $row[$email_i];
                        $contact = ClientContact::where('name', $row[$name_i])
                            ->whereHas('client_contact_phones', function ($q) use ($phone) {
                                $q->where('phone_number', 'like', $phone);
                            })
                            ->whereHas('client_contact_emails', function ($q) use ($email) {
                                $q->where('email', 'like', $email);
                            })->first();
                        if ($contact == null) {
                            $contact = new ClientContact;
                            $contact->a_attributes = array();

                            if ($company_exist && ClientContact::where('client_id', $company->id)->count() == 0) {
                                $contact->is_main_contact = true;
                            }

                            $contact->save();

                            foreach ($vals as $i => $type) {
                                switch ($type) {
                                    case 1:
                                        $contact->name = $row[$i];
                                        break;
                                    case 2:
                                        $phones = explode(',', $row[$i]);
                                        foreach ($phones as $phone) {
                                            $ccp = new ClientContactPhone;
                                            $ccp->phone_number = $phone;
                                            $ccp->client_contact_id = $contact->id;
                                            $ccp->save();
                                        }
                                        break;
                                    case 3:
                                        $emails = explode(',', $row[$i]);
                                        foreach ($emails as $email) {
                                            $ccp = new ClientContactEmail;
                                            $ccp->email = $email;
                                            $ccp->client_contact_id = $contact->id;
                                            $ccp->save();
                                        }
                                        break;
                                    case 4:
                                        $contact->note = $row[$i];
                                        break;
                                }
                            }
                            if ($contact->name == null) {
                                $contact->name = trans('template.Unknown');
                            }

                            if ($company_exist) {
                                $contact->client_id = $company->id;
                            }

                            $contact->save();
                        }
                    }
                    if ($service_exist) {
                        $service = new Service;
                        $service->generate_estimate_number();
                        foreach ($vals as $i => $type) {
                            switch ($type) {
                                case 9:
                                    $service->name = $row[$i];
                                    break;
                                case 10:
                                    $service->estimate_summ = $row[$i];
                                    break;
                                case 11:
                                    $user = User::where('name', $row[$i])->first();
                                    if ($user) {
                                        $service->responsible_user_id = $user->id;
                                    }
                                    break;
                                case 12:
                                    $service_state = ServiceState::where('name', $row[$i])->first();
                                    if ($service_state) {
                                        $service->service_state_id = $service_state->id;
                                    }
                                    break;
                                case 13:
                                    $dt = DateTime::createFromFormat('d.m.Y', $row[$i]);
                                    if ($dt !== false) {
                                        $service->created_at = Carbon::instance($dt);
                                    }
                                    break;
                                case 14:
                                    $service->note = $row[$i];
                                    break;
                            }
                        }
                        if ($service->service_state_id == null) {
                            $first_service_state = ServiceState::orderBy('order')->first();
                            $service->service_state_id = $first_service_state->id;
                        }
                        if ($service->responsible_user_id == null) {
                            $service->responsible_user_id = $current_user->id;
                        }
                        if ($service->service_priority_id == null) {
                            $first_service_priority = ServicePriority::first();
                            $service->service_priority_id = $first_service_priority->id;
                        }
                        if ($contact_exist) {
                            $service->client_contact_id = $contact->id;
                        }
                        if ($service->name == null) {
                            $service->name = trans('template.Unknown');
                        }
                        $service->save();
                    }
                }
            }

            $reader->close();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function import_estimate(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $current_user = Auth::user();
            $service_id = intval($request->input('service_id', 0));
            $service = Service::find($service_id);

            $reader = $this->get_reader_with_filename($request['src']);

            $estimate = new Estimate;
            $estimate->subject = 'Excel ' . $service->name;
            $estimate->deadline = 0;
            $estimate->additional_price = 0;
            $estimate->discount = 0;
            $estimate->user_id = $current_user->id;
            $estimate->blocked = false;
            $estimate->service_id = $service->id;
            $estimate->save();

            foreach ($reader->getSheetIterator() as $s_index => $sheet) {

                $previous_lines = [];
                $order_iterator = 0;

                foreach ($sheet->getRowIterator() as $r_index => $row) {
                    if ($r_index == 1) {
                        continue;
                    }

                    $estimate_line = new EstimateLine;
                    $estimate_line->estimate_id = $estimate->id;

                    $number = $row[0];
                    $description = $row[1];
                    $units = $row[2];
                    $quantity = doubleval($row[3] ?? 0);
                    $ppu = doubleval($row[4] ?? 0);

                    if (empty($units)) {

                        if (empty($number) && empty($description)) {
                            continue;
                        }

                        $estimate_line->lineable_type = '\App\EstimateLineCategory';
                        $estimate_line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineCategory';

                        $estimate_line_category = new EstimateLineCategory;
                        $estimate_line_category->name = $description;
                        $estimate_line_category->is_pattern = false;
                        $estimate_line_category->save();

                        $estimate_line->correct_lineable_id = $estimate_line_category->id;
                        $estimate_line->lineable_id = $estimate_line_category->id;
                    } else {
                        $estimate_line->lineable_type = '\App\EstimateLineData';
                        $estimate_line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineData';

                        $estimate_line_data = new EstimateLineData;
                        $estimate_line_data->description = $description;
                        $estimate_line_data->quantity = $quantity;
                        $estimate_line_data->ppu = $ppu;
                        if ($ppu > 0 && $quantity > 0) {
                            $estimate_line_data->price = $ppu * $quantity;
                        }

                        $estimate_unit = EstimateUnit::where('measure', 'like', '%' . $units . '%')->first();
                        if ($estimate_unit == null) {
                            throw new Exception('A unidade de medida ' . $units . ' não foi encontrada no banco de dados.');
                            $estimate_unit = new EstimateUnit;
                            $estimate_unit->measure = $units;
                            $estimate_unit->save();
                        }
                        $estimate_line_data->estimate_unit_id = $estimate_unit->id;
                        $estimate_line_data->save();

                        $estimate_line->correct_lineable_id = $estimate_line_data->id;
                        $estimate_line->lineable_id = $estimate_line_data->id;
                    }

                    $exploded_number = explode('.', $number);
                    $level = count($exploded_number);

                    if ($level > 1) {
                        foreach ($previous_lines as $previous_line) {
                            if ($previous_line['level_id'] < $level) {
                                $estimate_line->parent_id = $previous_line['line_id'];
                            }
                        }
                    }

                    $estimate_line->order = $order_iterator;
                    $estimate_line->save();

                    array_push($previous_lines, ['line_id' => $estimate_line->id, 'level_id' => $level]);
                    $order_iterator = $order_iterator + 1;
                }
            }

            $reader->close();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
