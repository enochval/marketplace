<?php

use App\Models\City;
use Illuminate\Database\Seeder;

class CitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cities = [
            "Abule", "Aruna", "Agric", "Adeniyi Jones", "Agege", "Agidingbi", "Aguda", "Ajah", "Ajegunle",
            "Ajeromi-Ifelodun", "Akerele ", "Akoka", "Allen", "Alaba", "Alagomeji", "Alausa", "Alimosho",
            "Amuwo Odofin", "Anthony Village", "Apapa", "Badagry", "Bariga", "Coker", "Dolphin Estate", "Dopemu",
            "Ebute Metta", "Epe", "Eti-Osa", "Festac Town", "Gbagada", "Idumota", "Ifako - Ijaiye", "Ijesha",
            "Ijora", "Ikeja", "Ikorodu", "Ikoyi", "Ilasamaja", "Ilupeju", "Iwaya", "Iyana Ipaja", "Jibowu", "Ketu",
            "Kosofe", "Ladipo", "Lagos Island", "Lagos Mainland", "Lawanson", "Lekki", "Marina", "Maryland", "Masha",
            "Maza Maza", "Mende", "Mile 2", "mile 12", "Mushin", "Obalende", "Obanikoro", "Ogba ", "Ogudu", "Ojo",
            "Ojodu", "Ojodu_Berger", " Ojota", "Ojuelegba", "Olodi", "Onigbongbo", "Onipanu", "Oniru", "Opebi",
            "Oregun", "Oshodi - Isolo", "Palmgrove", "Papa Ajao", "Sabo", "Satellite Town", " Shomolu", "Surulere",
            "Takwa Bay", "Tinubu Square", "Victoria Garden City", "Victoria", "Island", "Yaba"
        ];

        foreach ($cities as $city) {
            City::create([
                'name' => $city
            ]);
        }
    }
}
