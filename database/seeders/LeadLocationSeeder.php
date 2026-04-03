<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeadState;
use App\Models\LeadCity;

class LeadLocationSeeder extends Seeder
{
    public function run()
    {
        $stateCities = [
            'Andhra Pradesh' => ['Visakhapatnam','Vijayawada','Guntur','Nellore','Kurnool','Tirupati','Kakinada','Rajahmundry','Kadapa','Eluru'],
            'Arunachal Pradesh' => ['Itanagar','Naharlagun','Pasighat','Tawang','Ziro'],
            'Assam' => ['Guwahati','Silchar','Dibrugarh','Jorhat','Nagaon','Tinsukia','Tezpur','Bongaigaon'],
            'Bihar' => ['Patna','Gaya','Bhagalpur','Muzaffarpur','Purnia','Darbhanga','Arrah','Begusarai','Katihar','Munger'],
            'Chhattisgarh' => ['Raipur','Bhilai','Bilaspur','Korba','Durg','Rajnandgaon','Jagdalpur'],
            'Goa' => ['Panaji','Margao','Vasco da Gama','Mapusa','Ponda'],
            'Gujarat' => ['Ahmedabad','Surat','Vadodara','Rajkot','Bhavnagar','Jamnagar','Junagadh','Gandhinagar','Anand','Navsari','Mehsana','Morbi','Bharuch','Surendranagar'],
            'Haryana' => ['Gurgaon','Faridabad','Panipat','Ambala','Yamunanagar','Rohtak','Hisar','Karnal','Sonipat','Panchkula','Rewari'],
            'Himachal Pradesh' => ['Shimla','Manali','Dharamshala','Solan','Mandi','Hamirpur','Kullu'],
            'Jharkhand' => ['Ranchi','Jamshedpur','Dhanbad','Bokaro','Deoghar','Hazaribagh','Giridih'],
            'Karnataka' => ['Bengaluru','Mysuru','Hubballi','Mangaluru','Belagavi','Kalaburagi','Ballari','Vijayapura','Shivamogga','Tumakuru','Udupi','Dharwad','Davangere'],
            'Kerala' => ['Thiruvananthapuram','Kochi','Kozhikode','Kollam','Thrissur','Palakkad','Malappuram','Alappuzha','Kottayam','Kannur'],
            'Madhya Pradesh' => ['Bhopal','Indore','Jabalpur','Gwalior','Ujjain','Sagar','Ratlam','Satna','Murwara','Rewa','Dewas','Chhindwara'],
            'Maharashtra' => ['Mumbai','Pune','Nagpur','Nashik','Thane','Aurangabad','Solapur','Kolhapur','Amravati','Nanded','Akola','Jalgaon','Latur','Dhule','Ahmednagar','Chandrapur'],
            'Manipur' => ['Imphal','Thoubal','Bishnupur','Churachandpur'],
            'Meghalaya' => ['Shillong','Tura','Jowai'],
            'Mizoram' => ['Aizawl','Lunglei','Saiha'],
            'Nagaland' => ['Kohima','Dimapur','Mokokchung'],
            'Odisha' => ['Bhubaneswar','Cuttack','Rourkela','Berhampur','Sambalpur','Puri','Balasore','Bhadrak','Baripada'],
            'Punjab' => ['Ludhiana','Amritsar','Jalandhar','Patiala','Bathinda','Mohali','Hoshiarpur','Gurdaspur','Firozpur'],
            'Rajasthan' => ['Jaipur','Jodhpur','Udaipur','Kota','Bikaner','Ajmer','Bhilwara','Alwar','Bharatpur','Sikar','Pali','Sri Ganganagar'],
            'Sikkim' => ['Gangtok','Namchi','Mangan','Gyalshing'],
            'Tamil Nadu' => ['Chennai','Coimbatore','Madurai','Tiruchirappalli','Salem','Tirunelveli','Vellore','Erode','Thoothukudi','Dindigul','Karur','Cuddalore','Kancheepuram','Tiruppur'],
            'Telangana' => ['Hyderabad','Warangal','Nizamabad','Karimnagar','Ramagundam','Khammam','Mahbubnagar','Nalgonda','Adilabad'],
            'Tripura' => ['Agartala','Udaipur','Dharmanagar','Kailasahar'],
            'Uttar Pradesh' => ['Lucknow','Kanpur','Ghaziabad','Agra','Meerut','Varanasi','Allahabad','Prayagraj','Bareilly','Aligarh','Moradabad','Saharanpur','Gorakhpur','Noida','Mathura','Muzaffarnagar','Firozabad','Jhansi','Ayodhya'],
            'Uttarakhand' => ['Dehradun','Haridwar','Roorkee','Haldwani','Nainital','Rishikesh','Mussoorie'],
            'West Bengal' => ['Kolkata','Asansol','Siliguri','Durgapur','Bardhaman','Malda','Barasat','Krishnanagar','Howrah','Hooghly'],
            'Delhi' => ['New Delhi','Dwarka','Saket','Rohini','Janakpuri','Lajpat Nagar','Karol Bagh','Connaught Place','Pitampura','Shahdara'],
            'Chandigarh' => ['Chandigarh'],
            'Jammu and Kashmir' => ['Srinagar','Jammu','Anantnag','Sopore','Baramulla','Kathua'],
            'Ladakh' => ['Leh','Kargil'],
            'Puducherry' => ['Puducherry','Karaikal','Yanam','Mahé'],
            'Andaman and Nicobar Islands' => ['Port Blair'],
            'Dadra and Nagar Haveli' => ['Silvassa'],
            'Daman and Diu' => ['Daman','Diu'],
            'Lakshadweep' => ['Kavaratti']
        ];

        foreach ($stateCities as $stateName => $cities) {
            $state = LeadState::firstOrCreate(['name' => $stateName]);
            foreach ($cities as $cityName) {
                LeadCity::firstOrCreate([
                    'state_id' => $state->id,
                    'name' => $cityName
                ]);
            }
        }
    }
}
