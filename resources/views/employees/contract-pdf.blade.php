<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato Individual de Trabajo — {{ $employee->full_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10.5px; color: #1f2937; line-height: 1.45; margin: 0; padding: 28px 36px; }
        h1 { font-size: 13px; text-align: center; margin: 0 0 4px 0; text-transform: uppercase; }
        h2 { font-size: 11px; text-align: center; margin: 0 0 14px 0; font-weight: 600; }
        h3 { font-size: 11px; margin: 14px 0 6px 0; font-weight: 700; text-transform: uppercase; }
        p { margin: 0 0 6px 0; text-align: justify; }
        .uc { text-transform: uppercase; }
        .indent { text-indent: 24px; }
        table.beneficiary { width: 100%; border-collapse: collapse; margin: 6px 0; }
        table.beneficiary th, table.beneficiary td { border: 1px solid #d1d5db; padding: 5px 7px; font-size: 10px; text-align: left; }
        table.beneficiary th { background: #f3f4f6; font-weight: 700; }
        table.schedules { width: 100%; border-collapse: collapse; margin: 6px 0; }
        table.schedules th, table.schedules td { border: 1px solid #d1d5db; padding: 4px 6px; font-size: 9.5px; text-align: center; }
        table.schedules th { background: #f3f4f6; font-weight: 700; }
        .signatures { margin-top: 36px; display: table; width: 100%; }
        .sig-cell { display: table-cell; width: 50%; padding: 0 16px; text-align: center; vertical-align: top; }
        .sig-line { border-top: 1px solid #1f2937; margin-bottom: 4px; padding-top: 56px; }
        .sig-name { font-weight: 700; font-size: 10px; }
        .sig-role { font-size: 9px; color: #4b5563; margin-top: 2px; }
    </style>
</head>
<body>
    <h1>Contrato Individual de Trabajo por Tiempo Indeterminado con Periodo de Prueba de hasta 90 Días</h1>

    <p class="indent">
        CONTRATO INDIVIDUAL DE TRABAJO POR TIEMPO INDETERMINADO, QUE CELEBRAN POR UNA PARTE
        <strong>{{ $company['legal_name'] ?? '—' }}</strong>, REPRESENTADA POR CONDUCTO DEL C.
        <strong>{{ $company['legal_representative'] ?? '—' }}</strong>, EN SU CARÁCTER DE APODERADO LEGAL
        A QUIENES EN LO SUCESIVO Y PARA EFECTOS DEL PRESENTE CONTRATO SE LES DENOMINARÁ COMO
        <strong>“LA EMPRESA”</strong> Y POR LA OTRA <strong class="uc">{{ $employee->full_name }}</strong>
        A QUIEN EN LO SUCESIVO SE LE DENOMINARÁ COMO <strong>“EL TRABAJADOR”</strong>, AL TENOR DE LAS
        SIGUIENTES DECLARACIONES Y CLÁUSULAS:
    </p>

    <h3>Declaraciones</h3>

    <p><strong>I.- Declara “LA EMPRESA”:</strong> {{ $company['commercial_name'] ?? '—' }}</p>
    <p>a) Ser una persona moral debidamente constituida conforme a las Leyes Mexicanas, con domicilio ubicado en: <strong>{{ $company['address'] ?? '—' }}</strong>, con Registro Federal del Contribuyente: <strong>{{ $company['rfc'] ?? '—' }}</strong></p>
    <p>b) Tener como objeto social: {{ $company['business_object'] ?? 'las actividades relativas a la producción, comercialización y distribución de alimentos y bebidas.' }}</p>
    <p>c) Que cuenta para la consecución de su objeto social, con los elementos necesarios para cumplir con sus obligaciones legales frente a sus trabajadores.</p>

    <p><strong>II.- Declara “EL TRABAJADOR”:</strong></p>
    <p>a) Llamarse <strong class="uc">{{ $employee->full_name }}</strong>, de nacionalidad <span class="uc">{{ $employee->nationality ?: '—' }}</span>,
        @if($employee->birth_date)
            nacido el {{ $employee->birth_date->isoFormat('DD [de] MMMM [de] YYYY') }}, tener {{ $employee->age ?? '—' }} años de edad,
        @endif
        @if($employee->gender)
            sexo <span class="uc">{{ $employee->gender }}</span>,
        @endif
        @if($employee->marital_status)
            estado civil <span class="uc">{{ $employee->marital_status }}(A)</span>
        @endif
        y con domicilio en {{ $employee->address ?: '—' }}, con Registro Federal de Contribuyentes y Clave Única de Registro de Población <strong>{{ $employee->curp ?: '—' }}</strong>.
    </p>
    <p>b) Tener los conocimientos, aptitudes y experiencia necesarios para prestar los servicios requeridos por “LA EMPRESA”.</p>
    <p>c) Para efectos del pago de salarios y prestaciones devengadas y no cobradas en caso de muerte o desaparición derivada de un acto delincuencial, conforme a lo dispuesto en el artículo 501 de la Ley federal del trabajo, “EL TRABAJADOR” designa como beneficiario a:</p>

    <table class="beneficiary">
        <thead>
            <tr>
                <th>Nombre del beneficiario</th>
                <th>Parentesco</th>
                <th>Número de teléfono</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            @if($employee->beneficiary_name)
                <tr>
                    <td>{{ $employee->beneficiary_name }}</td>
                    <td>{{ $employee->beneficiary_relationship ?: '—' }}</td>
                    <td>{{ $employee->beneficiary_phone ?: '—' }}</td>
                    <td>{{ rtrim(rtrim(number_format((float) $employee->beneficiary_percentage, 2), '0'), '.') }}%</td>
                </tr>
            @else
                <tr><td colspan="4" style="text-align:center; font-style:italic;">Sin información a mostrar</td></tr>
            @endif
        </tbody>
    </table>

    <p><strong>III.-</strong> Ambas partes declaran que cuentan con la debida capacidad legal para celebrar el presente contrato, y que es su voluntad aceptar como condiciones las siguientes:</p>

    <h3>Cláusulas</h3>

    <p><strong>PRIMERA.-</strong> El presente Contrato Individual de Trabajo se celebra por TIEMPO INDETERMINADO CON UN PERIODO DE PRUEBA DE 90 DÍAS, con fundamento en lo dispuesto por el artículo 35 de la Ley Federal del Trabajo, sólo podrá ser modificado, suspendido, rescindido o terminado en los casos y con los requisitos señalados en la Ley Federal del Trabajo.</p>

    <p><strong>SEGUNDA.-</strong> Ambas partes acuerdan que el presente contrato de trabajo por TIEMPO INDETERMINADO CON UN PERIODO DE PRUEBA DE 90 DÍAS tendrá una vigencia a partir de
        @if($employee->hired_at)
            <strong>{{ $employee->hired_at->isoFormat('DD [de] MMMM [de] YYYY') }}</strong>,
        @else
            <strong>—</strong>,
        @endif
        respetando su antigüedad a partir de la misma.
    </p>

    <p><strong>TERCERA.-</strong> “EL TRABAJADOR” conviene en prestar sus servicios personales y subordinados a “LA EMPRESA” en el puesto de
        <strong class="uc">{{ $employee->position ?: '—' }}</strong>,
        desempeñando las funciones propias del puesto siendo estas enunciativas mas no limitativas por exigirlo así la naturaleza del trabajo. Así mismo, “EL TRABAJADOR” conviene expresamente en que desempeñará cualquier otra actividad que le indique “LA EMPRESA”, de acuerdo con las necesidades del servicio, sus conocimientos, habilidades, experiencia, capacitación, y grado de confianza depositada, siempre y cuando esta sea conexa a las actividades encomendadas al puesto asignado en el párrafo que antecede.</p>

    <p><strong>CUARTA.-</strong> “EL TRABAJADOR”, desempeñará sus servicios bajo la dirección y dependencia del “LA EMPRESA”, en el domicilio que este le indique, quedando obligado a observar todas las instrucciones, políticas, reglamentos, manuales que establezca o puedan establecerse en el futuro, y a vigilar que estos sean cumplidos por el personal a su cargo.</p>

    <p><strong>QUINTA.-</strong> Las partes convienen en que la jornada de trabajo será la máxima legal conforme a lo estipulado en el artículo 60 de la Ley Federal del Trabajo, los cuales podrán modificarse con previo aviso por escrito al trabajador.</p>

    <table class="schedules">
        <thead>
            <tr>
                <th>Personal de piso</th>
                <th>Personal de cocina</th>
                <th>Personal de planta alta</th>
                <th>Personal administrativo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>(7am a 3pm) — (3pm a 11pm) — (11am a 7pm) — (7am a 11am y 7pm a 11pm)</td>
                <td>(6:30am a 2:30pm) — (2:30pm a 10:30pm)</td>
                <td>7pm a 2am</td>
                <td>(8am a 5pm) — (9am a 2pm)</td>
            </tr>
        </tbody>
    </table>

    <p>Los horarios de trabajo son rotativos. La rotación de los horarios se definirá de acuerdo al cronograma de horarios.</p>

    <p><strong>SEXTA.-</strong> La duración de la jornada de trabajo será acorde a lo establecido en la Ley Federal del Trabajo, estableciéndose como descanso el día <strong>DOMINGO</strong> de cada semana, día de descanso que se pagará íntegro siempre y cuando el trabajador no incurra en faltas injustificadas en el periodo de seis días de trabajo que enuncia el artículo 69 de la Ley Federal del Trabajo, en el entendido que de incurrir el trabajador en faltas injustificadas el pago de su descanso se reducirá y se pagará en proporción a los días efectivamente laborados de conformidad con el artículo 72 de la Ley Federal del Trabajo. Lo anterior en la inteligencia que, de acuerdo a las necesidades de trabajo y a la rotación de los turnos, “EL TRABAJADOR” manifiesta su conformidad y autorización para que su jornada de labores sea modificada siempre y cuando se encuentre dentro de la jornada legal del artículo 60 de la Ley Federal del Trabajo y se le respeten en todo momento su categoría y salario.</p>

    <p><strong>SÉPTIMA.-</strong> “EL TRABAJADOR” únicamente podrá laborar tiempo extraordinario previa autorización de “LA EMPRESA” y mediante orden por escrito, en el que señalará el día y los horarios en el cual se desempeñará el mismo.</p>

    <p><strong>OCTAVA.-</strong> “EL TRABAJADOR” se obliga a sujetarse a cualquier tipo de control de asistencia que al efecto señale y reciba de “LA EMPRESA”.</p>

    <p><strong>NOVENA.-</strong> Las partes convienen en que “EL TRABAJADOR” percibirá de “LA EMPRESA” el pago de
        @if($dailySalary !== null)
            <strong>$ {{ number_format($dailySalary, 2) }}</strong> {{ $dailySalaryInWords }},
        @else
            <strong>—</strong>,
        @endif
        por concepto de salario diario, mismo que será pagado mediante moneda de curso legal de manera SEMANAL, CATORCENAL, QUINCENAL O MENSUAL en días y horas hábiles previa firma del recibo de salario correspondiente, donde se incluirá el pago del séptimo día y demás prestaciones a que tenga derecho.</p>

    <p><strong>DÉCIMA.-</strong> “EL TRABAJADOR” está de acuerdo en que sus salarios le sean cubiertos por “LA EMPRESA” mediante pago en efectivo en el domicilio de “LA EMPRESA” o transferencia electrónica a una cuenta bancaria de su propiedad, misma que se compromete a proporcionar a “LA EMPRESA” a más tardar en la fecha de pago, sirviendo el comprobante de la transferencia electrónica hecha por “LA EMPRESA” como recibo de pago del salario.</p>

    <p><strong>DÉCIMA PRIMERA.-</strong> “EL TRABAJADOR” y “LA EMPRESA” establecen que los únicos días en que “EL TRABAJADOR” no prestará sus servicios para la empresa, son los días de descanso obligatorio establecidos en el artículo 74 de la Ley Federal del Trabajo. En caso de laborar por mutuo acuerdo se pagarán conforme al artículo 75 de la Ley Federal del Trabajo.</p>

    <p><strong>DÉCIMA SEGUNDA.-</strong> Cuando “EL TRABAJADOR” haya cumplido el año de servicios prestados, tendrá derecho a un periodo de vacaciones de 12 días por lo menos, mismos que aumentarán 2 días por cada año de servicios prestados de acuerdo al artículo 78 y 81 de la Ley Federal del Trabajo, las cuales no podrán compensarse con una remuneración. Deberán de concederse a “EL TRABAJADOR” el periodo de vacaciones dentro de los 6 meses posteriores al cumplimiento conforme al acuerdo mutuo con “LA EMPRESA”. “EL TRABAJADOR” recibirá un 25% de prima sobre el salario que le corresponda por los días de vacaciones.</p>

    <p><strong>DÉCIMA TERCERA.-</strong> “EL TRABAJADOR” percibirá un aguinaldo anual, que deberá pagarse antes del veinte de diciembre, equivalente a quince días de salario por lo menos y cuando no haya cumplido el año de servicios tendrá derecho a que se le pague la parte proporcional al tiempo trabajado de conformidad con lo dispuesto en el artículo 87 de la Ley Federal del Trabajo.</p>

    <p><strong>DÉCIMA CUARTA.-</strong> Las partes se obligan a cumplir con los planes y programas de capacitación, adiestramiento y productividad que se establezca en el centro de trabajo de acuerdo con lo establecido en el capítulo III BIS artículo 153-A y demás relativos y aplicables de la Ley Federal del Trabajo.</p>

    <p><strong>DÉCIMA QUINTA.-</strong> “EL TRABAJADOR” se obliga expresamente a no divulgar, revelar, disponer o hacer uso con fines no autorizados o ajenos a “LA EMPRESA” por sí, por tercera persona o por cualquier otro medio, la información propiedad de “LA EMPRESA”, en cualquier forma en que tal información se represente, respecto de sus actividades, sean industriales, comerciales o administrativas, relacionadas con los negocios, políticas, sistemas, procesos, métodos, técnicas, especificaciones sobre maquinaria y equipo, inversiones, estadísticas, planes de ventas y desarrollo y demás secretos o información confidencial de “LA EMPRESA”, y, en general, cualquier dato que “EL TRABAJADOR” conozca o llegue a ser de su conocimiento con motivo de la prestación de los servicios contratados.</p>

    <p>“EL TRABAJADOR” se obliga a no adquirir interés alguno, participar o intervenir en los asuntos de empresas competidoras, proveedoras o consumidoras de los productos o servicios de “LA EMPRESA” sin la autorización previa y por escrito de “LA EMPRESA”; si “EL TRABAJADOR” no cumple con esta disposición, quedará sujeto a la responsabilidad civil y penal que cause a “LA EMPRESA”.</p>

    <p><strong>DÉCIMA SÉPTIMA.-</strong> “EL TRABAJADOR” está especialmente obligado a respetar y realizar las instrucciones y prácticas destinadas a prevenir riesgos de trabajo, bajo pena de las sanciones que determinen las leyes.</p>

    <p><strong>DÉCIMA OCTAVA.-</strong> “EL TRABAJADOR” tendrá derecho a gozar de un periodo comprendido de 30 minutos de acuerdo al programa de descanso que la “EMPRESA” comunicará cada mes, para descansar o disfrutar sus alimentos dentro de las instalaciones, los cuales serán computados como tiempo efectivo de la jornada de trabajo.</p>

    <p><strong>DÉCIMA NOVENA.-</strong> Las inasistencias a las labores por accidente o enfermedad sólo podrán ser justificadas por el trabajador mediante la exhibición del certificado de incapacidad expedido por un doctor acreditado, el cual deberá ser entregado a la “EMPRESA” el primer día en que aquél se reincorpore a sus labores. Lo anterior sin perjuicio de dar el aviso correspondiente el mismo día en que se produzca la falta.</p>

    <p><strong>VIGÉSIMA.-</strong> “EL TRABAJADOR” se obliga a avisar a “LA EMPRESA” de cualquier cambio de domicilio, entendiéndose que, de no hacerlo, cualquier notificación se le hará en el último domicilio que tenga registrado en “LA EMPRESA”, sometiéndose ambas partes desde ahora a la jurisdicción de los tribunales laborales de esta ciudad.</p>

    <p><strong>VIGÉSIMA PRIMERA.-</strong> “EL TRABAJADOR” se obliga a observar y respetar las disposiciones contenidas en el presente contrato y en el Reglamento Interior de Trabajo en su caso.</p>

    <p><strong>VIGÉSIMA SEGUNDA.-</strong> Cualquier otra situación no prevista en el presente contrato, las partes nos sujetaremos a las disposiciones de nuestra Constitución Política, la Ley Federal del Trabajo y demás leyes y reglamentos que emanen de la misma.</p>

    <p style="margin-top: 12px;">Leído por ambas partes y un testigo por cada uno el presente contrato y sabedores de las obligaciones que contraen, lo ratifican y firman en la ciudad de <strong>{{ $company['sign_city'] ?? '—' }}</strong>.</p>

    <div class="signatures">
        <div class="sig-cell">
            <div class="sig-line"></div>
            <div class="sig-name uc">{{ $company['legal_representative'] ?? '—' }}</div>
            <div class="sig-role">POR “LA EMPRESA”</div>
        </div>
        <div class="sig-cell">
            <div class="sig-line"></div>
            <div class="sig-name uc">{{ $employee->full_name }}</div>
            <div class="sig-role">“EL TRABAJADOR”</div>
        </div>
    </div>

    <div class="signatures">
        <div class="sig-cell">
            <div class="sig-line"></div>
            <div class="sig-role">TESTIGO POR “LA EMPRESA”</div>
        </div>
        <div class="sig-cell">
            <div class="sig-line"></div>
            <div class="sig-role">TESTIGO POR “EL TRABAJADOR”</div>
        </div>
    </div>
</body>
</html>
