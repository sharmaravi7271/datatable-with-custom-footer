<?php
//custom datatable ajax code for moodles

if($request =='courselist'){

    header("Content-Type: application/json");

    $request= $_GET;

//    cl.id,cl.stringid,cl.original,s.school_name
    $columns = array(

        0 => 'c.fullname',
        1 => 'cc.name',
        2 => 'ccm.timecompleted ',
    );

    $args = array(
        'posts_per_page' => $request['length'],
        'offset' => $request['start'],
        'order' => $request['order'][0]['dir'],
    );


    $args['meta_key'] = $columns[$request['order'][0]['column']];
    $args['orderby'] = $columns[$request['order'][0]['column']];




    if( !empty($request['search']['value']) ) { // When datatables search is used
        $args['meta_query'] = array(

//            array(
//                'key' => 's.school_name',
//                'value' => $request['search']['value'],
//                'compare' => 'LIKE'
//            ),
            array(
                'key' => 'c.fullname',
                'value' => $request['search']['value'],
                'compare' => 'LIKE'
            ),
            array(
                'key' => 'cc.name',
                'value' => $request['search']['value'],
                'compare' => 'LIKE'
            )
//



        );
    }


    $relation = $args['meta_query']['relation'];
    $where =array();


    foreach($args['meta_query'] as $meta){

        if($meta['key'] ==0) {
            $where[] = $meta['key'] . ' ' . $meta['compare'] . ' "%' . $meta['value'] . '%" ';
        }

    }



    $where_con = array();
    if(!empty($where)) {
        $where_str = implode(" OR ", $where);
    }


//    $where_con[] = "c.contextlevel =70 && length(f.filename)>1";

    if($where_str !="") {
        $where_con[] = $where_str;
    }


    if(!empty($where_con)) {
        $wherestr = " && " . implode(" &&  ", $where_con);

    }

    $wherestr .= " && c.visible=1";


    $offset= $args['offset'];
    $limit= $args['posts_per_page'];
    $order= $args['order'];
    $order= $args['order'];
    $orderby= $args['orderby'];
//    query for file data strat

// MAKE ARRAY OF ALL COURSE

    $studentid = $_GET['student'];

    $sql_cert = "SELECT id,course FROM mdl_certificate WHERE name='CERTIFICATE OF COMPLETION'";

    $arr_cert = $DB->get_records_sql($sql_cert);

    $cid = array();

    foreach ($arr_cert as $key_cert) {
        $cid[$key_cert->course] = $key_cert->id;
    }

    // select c.fullname as course,cc.name as category,ccm.id as certificateid, ciss.timecreated as certificateissuedtime from mdl_course c left join mdl_course_categories cc on cc.id = c.category left join mdl_certificate ccm on ccm.course = c.id left join mdl_certificate_issues ciss on ciss.certificateid = certificateid JOIN mdl_enrol en ON en.courseid = c.id JOIN mdl_user_enrolments ue ON ue.enrolid = en.id WHERE ccm.name="Certificate of Completion" AND ciss.userid={$_GET['student']} AND ue.userid={$_GET['student']}

//    $sql = "select c.id as cid,c.fullname as course,cc.name as category,cc.id as courseid, cc.idnumber as clockhrs, cd.degrees as degrees, '' as timecompleted, 'lnm' as lnm, 'dmm' as dmm, 'dnm' as dnm, 'omd' as omd, 'datim' as datim, 'dma' as dma, 'dd' as dd, 'da' as da, 'phd' as phd, 'reshours' as reshours, 'awards' as awards from mdl_course c left join mdl_course_categories cc on cc.id = c.category JOIN mdl_enrol en ON en.courseid = c.id JOIN mdl_user_enrolments ue ON ue.enrolid = en.id JOIN mdl_coursedegree cd ON cd.id = cc.id WHERE ue.userid={$_GET['student']}  {$wherestr} order by {$orderby} {$order} limit {$offset} ,{$limit}";
    $sql = "select c.id as cid,c.idnumber as course_clockhrs ,c.fullname as course,cc.name as category,cc.id as courseid, cc.idnumber as clockhrs, cd.degrees as degrees, '' as timecompleted, 'lnm' as lnm, 'dmm' as dmm, 'dnm' as dnm, 'omd' as omd, 'datim' as datim, 'dma' as dma, 'dd' as dd, 'da' as da, 'phd' as phd, 'reshours' as reshours, 'awards' as awards from mdl_course c left join mdl_course_categories cc on cc.id = c.category JOIN mdl_enrol en ON en.courseid = c.id JOIN mdl_user_enrolments ue ON ue.enrolid = en.id JOIN mdl_coursedegree cd ON cd.id = cc.id WHERE ue.userid={$_GET['student']}  {$wherestr} order by {$orderby} {$order} ";

    /*   $sql = "select  c.fullname as course,cc.name as category,ccm.timecompleted from mdl_course c left join mdl_course_categories cc on  cc.id = c.category left join mdl_course_completions ccm on ccm.course = c.id  JOIN mdl_enrol en ON en.courseid = c.id
    JOIN mdl_user_enrolments ue ON ue.enrolid = en.id  WHERE ue.userid= {$_GET['student']} {$wherestr} order by {$orderby} {$order} limit {$offset} ,{$limit}"; */
    $files = $DB->get_records_sql($sql);

    function secondsToTime($seconds,$type=0) {
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");

        if($type == 1){
            return $dtF->diff($dtT)->format('%a');
        }else if($type == 2){
            return $dtF->diff($dtT)->format('%h');
        }else{
            return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
        }

    }


    $clockhourstotal = 0;
    $licentiatetotal = 0;
    $dmmtotal = 0;
    $dnmtotal = 0;
    $omdtotal = 0;
    $datimtotal = 0;
    $dmatotal = 0;
    $ddtotal = 0;
    $datotal = 0;
    $phdtotal = 0;
    $awardstotal = 0;
    $resourcehourstotal = 0;



    foreach ($files as $key_files) {
        $courseid = $key_files->cid;
        $idnumber = $key_files->clockhrs;
        $c_idnumber = $key_files->course_clockhrs;
        $degrees = "X," . $key_files->degrees;
        $tick = "Req.";


        $hour = explode("_",$key_files->idnumber)[1];




        ## find user course enrol, days difference and award points (end)
        $licentiate = "";
        if (strpos($degrees,"LNM")>0) {
            $licentiate = $tick;
        } else {
            $licentiate = "";
        }

        $dmm = "";
        if (strpos($degrees,"DMM")>0) {
            $dmm = $tick;
        } else {
            $dmm = "";
        }

        $dnm = "";
        if (strpos($degrees,"DNM")>0) {
            $dnm = $tick;
        } else {
            $dnm = "";
        }

        $omd = "";
        if (strpos($degrees,"OMD")>0) {
            $omd = $tick;
        } else {
            $omd = "";
        }


        $datim = "";
        if (strpos($degrees,"DATIM")>0) {
            $datim = $tick;
        } else {
            $datim = "";
        }

        $dma = "";
        if (strpos($degrees,"DMA")>0) {
            $dma = $tick;
        } else {
            $dma = "";
        }

        $dd = "";
        if (strpos($degrees,"DD")>0) {
            $dd = $tick;
        } else {
            $dd = "";
        }

        $da = "";
        if (strpos($degrees,"DA")>0) {
            $da = $tick;
        } else {
            $da = "";
        }

        $phd = "";
        if (strpos($degrees,"phd")>0) {
            $phd = $tick;
        } else {
            $phd = "";
        }  $awards = "";
        if (strpos($degrees,"awards")>0) {
            $awards = $tick;
        } else {
            $awards = "";
        }

        // Get user resource hours
        $selreshours = $DB->get_records_sql('select * from {course_resourcehours_user} where userid='.$studentid.' and courseid='.$courseid);
        $resourcehours = 0;$clockhours = 0;
        foreach($selreshours as $reshours){

            $resourcehours = $reshours->resourcehours;
            $clockhours = $reshours->clockhours;
            $licentiate = $reshours->licentiate;
            $dmm = $reshours->dmm;
            $dnm = $reshours->dnm;
            $omd = $reshours->omd;
            $datim = $reshours->datim;
            $dma = $reshours->dma;
            $dd = $reshours->dd;
            $da = $reshours->doctor_arts;
            $phd = $reshours->phd;
            $awards = $reshours->awardspoints;




            //totals
            $clockhourstotal += $clockhours;
            $licentiatetotal += $licentiate;
            $dmmtotal += $dmm;
            $dnmtotal += $dnm;
            $omdtotal += $omd;
            $datimtotal += $datim;
            $dmatotal += $licentiate+$dmm+$dnm+$omd+$datim;
            $ddtotal += $licentiate+$dmm+$dnm+$omd+$datim;
            $datotal += $da;
            $phdtotal += $phd;
            $awardstotal += $awards;
            $resourcehourstotal += $resourcehours;

        }


        $pieces_idnumber = explode("_",$c_idnumber);
        if (is_siteadmin()) {
            $key_files->clockhrs = "<textarea onkeyup='updateValue(\"clockhours\",".$courseid.",".$studentid.",this.value)' onkeypress='return isNumberKey(event)' style='resize: none;height:20px;width: 80px;' >".$clockhours."</textarea>";
        }else{
            $key_files->clockhrs = $clockhours;
        }

        if (is_siteadmin()) {
            $key_files->reshours = "<textarea onkeyup='updateResourceHours(".$courseid.",".$studentid.",this.value)' onkeypress='return isNumberKey(event)' style='resize: none;height:20px;width: 80px;' >".$resourcehours."</textarea>";
        }else{
            $key_files->reshours = $resourcehours;
        }
        $sql_certissued = "SELECT * FROM mdl_certificate_issues WHERE certificateid=" . $cid[$key_cert->course] . " AND userid=" . $studentid;

        $issuedtime = 0;
        if ($arr_certissued = $DB->get_record_sql($sql_certissued)) {
            $timecreated = date("m/d/Y", $arr_certissued->timecreated);
            $key_files->timecompleted = $timecreated;
            $issuedtime = $arr_certissued->timecreated;


        } else {
            $key_files->timecompleted = "Not issued";

            $issuedtime = 0;
        }

        ## find user course enrol, days difference and award points (start)
        $userenrol_sql = "SELECT ue.*
            FROM {user_enrolments} ue,
            {enrol} e,
            {user} u
            WHERE u.id='$studentid'
            AND ue.userid=u.id
            AND e.courseid=$courseid
            AND ue.enrolid=e.id";


        $userenrolData = $DB->get_records_sql($userenrol_sql);
        $enroltime = 0;
        foreach($userenrolData as $uenrol){
            $enroltime = $uenrol->timecreated;
        }
        $days = 0;$hours = 0;
        if($issuedtime > 0){

            $datediff = $issuedtime - $enroltime;

            $days =  round($datediff / (60 * 60 * 24));
            $days = secondsToTime($datediff,1);
            $hours = ".".secondsToTime($datediff,2);

            // if($courseid == 183){$days = 12;$hours=".4";}
            // if($courseid == 191){$days = 45; $hours = ".9";}

            $days = (float)$days + (float)$hours;
            //
            /*    if($key_files->clockhrs == 25){
                    if($days == 12){
                        if($hours == 12){
                           $days = 12.5;
                        }else if($hours < 12){
                            $days = 12.4;
                        }else if($hours > 12){
                            $days = 12.6;
                        }
                    }
                }*/

            // echo "issuedtime : ".$issuedtime.", enroltime : ".$enroltime.", days : ".$days.", timecreated : ".$key_files->timecompleted.", enrol : ".date("m/d/Y", $enroltime);
            // die;
        }

        $awardsPoints = 0;

        $compareday1 = 0;
        $compareday2 = 0;
        $compareday3 = 0;
        // $clockhours = $key_files->clockhrs;


        if($clockhours > 0){

            $compareday1 = (float)$clockhours/2;
            $compareday2 = $clockhours;
            $compareday3 = $clockhours*2;

            if($days > 0 && $days <= $compareday1){
                $awardsPoints = $clockhours*2;
            }else if($days > $compareday1 && $days <= $compareday2){
                $awardsPoints = $clockhours;
            }else if($days > $compareday1 && $days <= $compareday3){
                $awardsPoints = (float)$clockhours/2;
            }

        }



        if (is_siteadmin()) {


            $totalcount = $licentiate+$dmm+$dnm+$omd+$datim;
            $key_files->lnm = "  <input type='checkbox' name='licentiate' value='Req.' onclick='updateValuecheck(\"licentiate\",".$courseid.",".$studentid.",".$pieces_idnumber[1].",$(this))' ".($licentiate?'checked':'')."><textarea onkeyup='updateValue(\"licentiate\",".$courseid.",".$studentid.",this.value)' class='testarea4' style='resize: none;height:20px;width: 80px;' disabled >".$licentiate."</textarea>";
            $key_files->dmm = "<input type='checkbox' name='dmm' value='Req.' onclick='updateValuecheck(\"dmm\",".$courseid.",".$studentid.",".$pieces_idnumber[1].",$(this))' ".($dmm?'checked':'')."> <textarea onkeyup='updateValue(\"dmm\",".$courseid.",".$studentid.",this.value,$(this))' style='resize: none;height:20px;width: 50px;' disabled>".$dmm."</textarea>";
            $key_files->dnm = "<input type='checkbox' name='dnm' value='Req.' onclick='updateValuecheck(\"dnm\",".$courseid.",".$studentid.",".$pieces_idnumber[1].",$(this))' ".($dnm?'checked':'')."><textarea onkeyup='updateValue(\"dnm\",".$courseid.",".$studentid.",this.value)' style='resize: none;height:20px;width: 50px;' disabled >".$dnm."</textarea>";
            $key_files->omd = "<input type='checkbox' name='omd' value='Req.' onclick='updateValuecheck(\"omd\",".$courseid.",".$studentid.",".$pieces_idnumber[1].",$(this))' ".($omd?'checked':'')." ><textarea style='resize: none;height:20px;width: 50px;' disabled >".$omd."</textarea>";
            $key_files->datim = "<input type='checkbox' name='datim' value='Req.' onclick='updateValuecheck(\"datim\",".$courseid.",".$studentid.",".$pieces_idnumber[1].",$(this))' ".($datim?'checked':'')."><textarea onkeyup='updateValue(\"datim\",".$courseid.",".$studentid.",this.value)' style='resize: none;height:20px;width: 80px;'  disabled>".$datim."</textarea>";
//            $key_files->dma = "<textarea onkeyup='updateValue(\"dma\",".$courseid.",".$studentid.",this.value)'  style='resize: none;height:20px;width: 80px;' >".$dma."</textarea>";
//            $key_files->dma = "<textarea onkeyup='updateValue(\"dma\",".$courseid.",".$studentid.",this.value)'  style='resize: none;height:20px;width: 80px;' >".$totalcount."</textarea>";
//            $key_files->dd = "<textarea onkeyup='updateValue(\"dd\",".$courseid.",".$studentid.",this.value)' style='resize: none;height:20px;width: 80px;' >".$dd."</textarea>";
//            $key_files->dd = "<textarea onkeyup='updateValue(\"dd\",".$courseid.",".$studentid.",this.value)' style='resize: none;height:20px;width: 80px;' >".$totalcount."</textarea>";
            $key_files->dma = $totalcount;
            $key_files->dd = $totalcount;
            $key_files->da = "<textarea onkeyup='updateValue(\"doctor_arts\",".$courseid.",".$studentid.",this.value)'  style='resize: none;height:20px;width: 80px;' >".$da."</textarea>";
            $key_files->phd = "<textarea onkeyup='updateValue(\"phd\",".$courseid.",".$studentid.",this.value)'  style='resize: none;height:20px;width: 80px;' >".$phd."</textarea>";
            $key_files->awards = "<textarea onkeyup='updateValue(\"awardspoints\",".$courseid.",".$studentid.",this.value)'  style='resize: none;height:20px;width: 80px;' >".$awards."</textarea>";
        }else{
            $key_files->lnm = $licentiate;
            $key_files->dmm = $dmm;
            $key_files->dnm = $dnm;
            $key_files->omd = $omd;
            $key_files->datim = $datim;
            $key_files->dma = $dma;
            $key_files->dd = $dd;
            $key_files->da = $da;
            $key_files->phd = $phd;
            $key_files->awards = $awards;
        }

//        $key_files->awards = $awardsPoints." points";

    }

    $sql = "SELECT count(*) as filescount from mdl_course c left join mdl_course_categories cc on  cc.id = c.category left join mdl_course_completions ccm on ccm.course = c.id  JOIN mdl_enrol en ON en.courseid = c.id 
JOIN mdl_user_enrolments ue ON ue.enrolid = en.id  WHERE ue.userid= {$_GET['student']} ";
    $count = $DB->get_record_sql($sql);

    $sql = "SELECT count(*) as filescount from mdl_course c left join mdl_course_categories cc on  cc.id = c.category left join mdl_course_completions ccm on ccm.course = c.id  JOIN mdl_enrol en ON en.courseid = c.id 
JOIN mdl_user_enrolments ue ON ue.enrolid = en.id  WHERE ue.userid= {$_GET['student']}  {$wherestr}";
    $countfilter = $DB->get_record_sql($sql);

    if($array ==null){
        $array = array();
    }
    foreach ($files as $file) {



//        $file->original ="<textarea disabled   rows='10' id='original_".$file->id."' style='width:300px'>".$file->original ."</textarea>";
//        $file->translation ="<textarea disabled   rows='10' id='teranslation_".$file->id."' style='width:300px'>".$translations[$file->stringid]."</textarea>";
//        $file->stringid ="<span   id='stringid_".$file->id."'>".$file->stringid."</span>";
//
////        <button class="btn btn-danger delete_tranalstion" id="delete_tranalstion_'.$file->id.'"> <i class="fa fa-trash" aria-hidden="true"></i></button>
//        $file->operations = '<button class="btn btn-success save_tranalstion" id="savetranalstion_'.$file->id.'"  style="display:none;"> <i class="fa fa-save" aria-hidden="true"></i></button><button class="btn btn-success edit_tranalstion" id="tranalstion_'.$file->id.'" > <i class="fa fa-pencil" aria-hidden="true"></i></button> ';


        $array[] = $file;
    }


    $json_data = array(
        "draw" => intval($request['draw']),
        "recordsTotal" => $count->filescount,
        "recordsFiltered" =>$countfilter->filescount,
        "data" => $array,
        "total" => array('clockhourstotal'=>$clockhourstotal,'licentiatetotal'=>$licentiatetotal,'dmmtotal'=>$dmmtotal,'dnmtotal'=>$dnmtotal,
            'omdtotal'=>$omdtotal,'datimtotal'=>$datimtotal,'dmatotal'=>$dmatotal,'ddtotal'=>$ddtotal,'datotal'=>$datotal,'phdtotal'=>$phdtotal,'awardstotal'=>$awardstotal,'resourcehourstotal'=>$resourcehourstotal),
    );


    echo json_encode($json_data);
    exit;

}