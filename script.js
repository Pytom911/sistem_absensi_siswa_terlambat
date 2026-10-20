/* =====================================================
   DATA SISWA
===================================================== */

const students = [

  {
    nis: "2201234567",
    name: "Yusuf Pratama",
    hadir: 17,
    izin: 2,
    sakit: 1
  },

  {
    nis: "2201234568",
    name: "Salma Aulia",
    hadir: 15,
    izin: 3,
    sakit: 2
  },

  {
    nis: "2201234569",
    name: "Rizky Maulana",
    hadir: 18,
    izin: 1,
    sakit: 1
  },

  {
    nis: "2201234570",
    name: "Aulia Rahma",
    hadir: 16,
    izin: 2,
    sakit: 2
  },

  {
    nis: "2201234571",
    name: "Fajar Nugraha",
    hadir: 14,
    izin: 4,
    sakit: 2
  },

  {
    nis: "2201234572",
    name: "Nadia Putri",
    hadir: 19,
    izin: 1,
    sakit: 0
  },

  {
    nis: "2201234573",
    name: "Dimas Saputra",
    hadir: 15,
    izin: 1,
    sakit: 4
  },

  {
    nis: "2201234574",
    name: "Siti Nurhaliza",
    hadir: 17,
    izin: 2,
    sakit: 1
  },

  {
    nis: "2201234575",
    name: "Ahmad Fauzi",
    hadir: 16,
    izin: 2,
    sakit: 1
  },

  {
    nis: "2201234576",
    name: "Budi Santoso",
    hadir: 18,
    izin: 1,
    sakit: 1
  },

  {
    nis: "2201234577",
    name: "Citra Dewi",
    hadir: 17,
    izin: 2,
    sakit: 1
  },

  {
    nis: "2201234578",
    name: "Dimas Pratama",
    hadir: 15,
    izin: 2,
    sakit: 2
  },

  {
    nis: "2201234579",
    name: "Eka Putri",
    hadir: 14,
    izin: 3,
    sakit: 2
  },

  {
    nis: "2201234580",
    name: "Fajar Hidayat",
    hadir: 19,
    izin: 1,
    sakit: 0
  },

  {
    nis: "2201234581",
    name: "Gina Aulia",
    hadir: 16,
    izin: 1,
    sakit: 2
  },

  {
    nis: "2201234582",
    name: "Hadi Kurniawan",
    hadir: 17,
    izin: 1,
    sakit: 2
  },

  {
    nis: "2201234583",
    name: "Indah Lestari",
    hadir: 18,
    izin: 1,
    sakit: 1
  },

  {
    nis: "2201234584",
    name: "Joko Prasetyo",
    hadir: 15,
    izin: 3,
    sakit: 1
  },

  {
    nis: "2201234585",
    name: "Kiki Amelia",
    hadir: 17,
    izin: 2,
    sakit: 1
  },

  {
    nis: "2201234586",
    name: "Lutfi Ramadhan",
    hadir: 16,
    izin: 2,
    sakit: 1
  },

  {
    nis: "2201234587",
    name: "Maya Sari",
    hadir: 19,
    izin: 1,
    sakit: 0
  },

  {
    nis: "2201234588",
    name: "Nanda Saputra",
    hadir: 16,
    izin: 2,
    sakit: 2
  },

  {
    nis: "2201234589",
    name: "Oktaviani",
    hadir: 15,
    izin: 2,
    sakit: 2
  },

  {
    nis: "2201234590",
    name: "Putra Maulana",
    hadir: 18,
    izin: 1,
    sakit: 1
  },

  {
    nis: "2201234591",
    name: "Qori Rahman",
    hadir: 17,
    izin: 1,
    sakit: 1
  },

  {
    nis: "2201234592",
    name: "Rani Aprilia",
    hadir: 15,
    izin: 3,
    sakit: 1
  },

  {
    nis: "2201234593",
    name: "Sandy Wijaya",
    hadir: 18,
    izin: 1,
    sakit: 0
  },

  {
    nis: "2201234594",
    name: "Tasya Nurfadilah",
    hadir: 16,
    izin: 2,
    sakit: 1
  },

  {
    nis: "2201234595",
    name: "Umar Faruq",
    hadir: 17,
    izin: 1,
    sakit: 1
  },

  {
    nis: "2201234596",
    name: "Vina Maulida",
    hadir: 19,
    izin: 1,
    sakit: 0
  }

];


/* =====================================================
   JADWAL
===================================================== */

const schedule = [

  {
    day: "Senin",

    lessons: [
      ["07:00 - 08:30", "Pemrograman Web", "Lab RPL"],
      ["08:30 - 10:00", "Basis Data", "Lab RPL"],
      ["10:15 - 11:45", "Bahasa Inggris", "Ruang 2"]
    ]

  },

  {
    day: "Selasa",

    lessons: [
      ["07:00 - 08:30", "Jaringan Komputer", "Lab Jaringan"],
      ["08:30 - 10:00", "Pemrograman Mobile", "Lab RPL"],
      ["10:15 - 11:45", "Matematika", "Ruang 2"]
    ]

  },

  {
    day: "Rabu",

    lessons: [
      ["07:00 - 08:30", "Sistem Informasi", "Ruang 1"],
      ["08:30 - 10:00", "PBO", "Lab RPL"],
      ["10:15 - 11:45", "Bahasa Indonesia", "Ruang 2"]
    ]

  },

  {
    day: "Kamis",

    lessons: [
      ["07:00 - 08:30", "UI/UX Design", "Lab RPL"],
      ["08:30 - 10:00", "PKK", "Ruang 3"],
      ["10:15 - 11:45", "Pendidikan Agama", "Ruang 2"]
    ]

  },

  {
    day: "Jumat",

    lessons: [
      ["07:00 - 08:30", "PJOK", "Lapangan"],
      ["08:30 - 10:00", "Produktif RPL", "Lab RPL"]
    ]

  }

];


/* =====================================================
   RIWAYAT
===================================================== */

let history =
  JSON.parse(
    localStorage.getItem("attendanceHistory") || "null"
  ) ||

  [

    {
      date: "28 Mei 2026",
      subject: "Pemrograman Web",
      time: "07:00 - 08:30",
      status: "Hadir"
    },

    {
      date: "27 Mei 2026",
      subject: "Basis Data",
      time: "08:30 - 10:00",
      status: "Hadir"
    },

    {
      date: "26 Mei 2026",
      subject: "Jaringan Komputer",
      time: "07:00 - 08:30",
      status: "Izin"
    },

    {
      date: "25 Mei 2026",
      subject: "Sistem Informasi",
      time: "10:15 - 11:45",
      status: "Hadir"
    },

    {
      date: "22 Mei 2026",
      subject: "Matematika",
      time: "08:30 - 10:00",
      status: "Sakit"
    }

  ];


/* =====================================================
   HELPER
===================================================== */

const $ = id =>
  document.getElementById(id);


const currentStudent =
  students[0];


/* =====================================================
   DATA ABSENSI TABEL
===================================================== */

let attendanceData = {};


/* =====================================================
   DEFAULT ABSENSI
===================================================== */

function createDefaultAttendance() {

  const data = {};

  students.forEach((student, index) => {

    let status = "Alpha";

    let jamMasuk = "";

    let jamKeluar = "";

    let keterangan = "";


    if (index % 5 === 0) {

      status = "Hadir";

      jamMasuk = "07:00";

      jamKeluar = "15:00";

    }


    else if (index % 5 === 1) {

      status = "Izin";

      keterangan = "Keperluan keluarga";

    }


    else if (index % 5 === 2) {

      status = "Hadir";

      jamMasuk = "06:58";

      jamKeluar = "15:00";

    }


    else if (index % 5 === 3) {

      status = "Sakit";

      keterangan = "Kurang sehat";

    }


    else {

      status = "Terlambat";

      jamMasuk = "07:15";

      jamKeluar = "15:00";

      keterangan = "Terlambat";

    }


    data[student.nis] = {

      status,

      jamMasuk,

      jamKeluar,

      keterangan

    };

  });


  return data;

}


/* =====================================================
   LOAD ABSENSI
===================================================== */

function loadAttendanceData() {

  const saved =
    localStorage.getItem(
      "attendance_xii_rpl1"
    );


  if (saved) {

    attendanceData =
      JSON.parse(saved);

  }

  else {

    attendanceData =
      createDefaultAttendance();

    saveAttendanceData();

  }

}


/* =====================================================
   SAVE ABSENSI
===================================================== */

function saveAttendanceData() {

  localStorage.setItem(
    "attendance_xii_rpl1",
    JSON.stringify(attendanceData)
  );

}


/* =====================================================
   TANGGAL
===================================================== */

function getTodayDate() {

  const today =
    new Date();

  const year =
    today.getFullYear();

  const month =
    String(
      today.getMonth() + 1
    ).padStart(2, "0");

  const day =
    String(
      today.getDate()
    ).padStart(2, "0");


  return `${year}-${month}-${day}`;

}


/* =====================================================
   LOGIN
===================================================== */

function login(e) {

  e.preventDefault();


  const username =
    $("username").value.trim();

  const password =
    $("password").value;


  if (
    (
      username === "2201234567" ||
      username.toLowerCase() === "yusuf"
    ) &&
    password === "123456"
  ) {

    $("loginPage")
      .classList
      .add("hidden");


    $("app")
      .classList
      .remove("hidden");


    showToast(
      "Login berhasil. Selamat datang!"
    );

  }

  else {

    showToast(
      "Username atau password salah."
    );

  }

}


/* =====================================================
   LOGOUT
===================================================== */

function logout() {

  $("app")
    .classList
    .add("hidden");


  $("loginPage")
    .classList
    .remove("hidden");


  $("password").value = "";


  showToast(
    "Anda telah logout."
  );

}


/* =====================================================
   NAVIGASI
===================================================== */

function navigate(page) {

  document
    .querySelectorAll(".page")
    .forEach(
      x =>
        x.classList.remove(
          "active-page"
        )
    );


  const target =
    $(page);


  if (target) {

    target.classList.add(
      "active-page"
    );

  }


  document
    .querySelectorAll(".nav-item")
    .forEach(
      x =>
        x.classList.toggle(
          "active",
          x.dataset.page === page
        )
    );


  document
    .querySelector(".sidebar")
    ?.classList.remove(
      "open"
    );


  if (page === "dashboard") {

    renderDashboard();

  }


  if (page === "absensi") {

    renderAttendanceTable();

  }

}


/* =====================================================
   TOAST
===================================================== */

function showToast(text) {

  const toast =
    $("toast");


  toast.textContent =
    text;


  toast.classList.add(
    "show"
  );


  clearTimeout(
    window.toastTimer
  );


  window.toastTimer =
    setTimeout(
      () =>
        toast.classList.remove(
          "show"
        ),
      2800
    );

}


/* =====================================================
   HITUNG DATA
===================================================== */

function getCounts() {

  const hadir =
    currentStudent.hadir;

  const izin =
    currentStudent.izin;

  const sakit =
    currentStudent.sakit;

  const alpha =
    20 -
    hadir -
    izin -
    sakit;


  const today =
    history.find(
      x =>
        x.date ===
        todayString()
    );


  return {
    hadir,
    izin,
    sakit,
    alpha,
    today
  };

}


/* =====================================================
   TANGGAL HARI INI
===================================================== */

function todayString() {

  return new Date()
    .toLocaleDateString(
      "id-ID",
      {
        day: "2-digit",
        month: "long",
        year: "numeric"
      }
    );

}


/* =====================================================
   RENDER SEMUA
===================================================== */

function renderAll() {

  $("welcomeName").textContent =
    currentStudent.name;

  $("headerName").textContent =
    currentStudent.name;

  $("profileName").textContent =
    currentStudent.name;


  $("todayDate").textContent =
    new Date()
      .toLocaleDateString(
        "id-ID",
        {
          weekday: "long",
          day: "numeric",
          month: "long",
          year: "numeric"
        }
      );


  renderDashboard();

  renderHistory();

  renderStudents();

  renderSchedule();

  renderAttendanceTable();

}


/* =====================================================
   DASHBOARD
===================================================== */

function renderDashboard() {

  const c =
    getCounts();


  const percent =
    Math.round(
      c.hadir / 20 * 100
    );


  $("attendancePercent")
    .textContent =
    percent + "%";


  $("hadirTotal")
    .textContent =
    c.hadir;


  $("izinTotal")
    .textContent =
    c.izin;


  $("sakitTotal")
    .textContent =
    c.sakit;


  $("donutPercent")
    .textContent =
    percent + "%";


  $("legendHadir")
    .textContent =
    c.hadir;


  $("legendIzin")
    .textContent =
    c.izin;


  $("legendSakit")
    .textContent =
    c.sakit;


  $("legendAlpha")
    .textContent =
    c.alpha;


  const h =
    c.hadir / 20 * 100;

  const i =
    (
      c.hadir +
      c.izin
    ) / 20 * 100;

  const s =
    (
      c.hadir +
      c.izin +
      c.sakit
    ) / 20 * 100;


  $("donut").style.background =
    `conic-gradient(
      #1764dd 0 ${h}%,
      #f5b51b ${h}% ${i}%,
      #ef4444 ${i}% ${s}%,
      #aeb7c4 ${s}% 100%
    )`;


  const day =
    new Date()
      .toLocaleDateString(
        "id-ID",
        {
          weekday: "long"
        }
      );


  const item =
    schedule.find(
      x =>
        x.day.toLowerCase() ===
        day.toLowerCase()
    ) ||
    schedule[0];


  $("todaySchedule")
    .innerHTML =

    item.lessons
      .slice(0, 2)
      .map(
        (lesson, i) => `

          <div class="schedule-item">

            <div class="time">

              ${lesson[0]
                .replace(
                  " - ",
                  "<br>"
                )}

            </div>

            <div>

              <h4>
                ${lesson[1]}
              </h4>

              <p>
                ${lesson[2]}
              </p>

              <small>
                Guru: Budi Santoso, S.Kom.
              </small>

            </div>

            <span class="status-live">

              ${
                i === 0
                  ? "Berlangsung"
                  : "Akan Datang"
              }

            </span>

          </div>

        `
      )
      .join("");

}


/* =====================================================
   ABSENSI SISWA
===================================================== */

function takeAttendance(status) {

  const today =
    todayString();


  const existing =
    history.find(
      x =>
        x.date === today &&
        x.subject ===
        "Pemrograman Web"
    );


  if (existing) {

    showToast(
      "Anda sudah melakukan absensi hari ini."
    );

    return;

  }


  history.unshift({

    date: today,

    subject: "Pemrograman Web",

    time: "07:00 - 08:30",

    status

  });


  localStorage.setItem(
    "attendanceHistory",
    JSON.stringify(history)
  );


  if (status === "Hadir")
    currentStudent.hadir++;


  if (status === "Izin")
    currentStudent.izin++;


  if (status === "Sakit")
    currentStudent.sakit++;


  showToast(
    `Absensi berhasil: ${status}`
  );


  renderAll();

}


/* =====================================================
   STATUS HTML
===================================================== */

function statusHTML(status) {

  return `
    <span
      class="status-badge status-${status.toLowerCase()}"
    >
      ${status}
    </span>
  `;

}


/* =====================================================
   RIWAYAT
===================================================== */

function renderHistory() {

  const filter =
    $("historyFilter").value;


  const data =
    filter === "Semua"
      ? history
      : history.filter(
          x =>
            x.status === filter
        );


  $("historyTable")
    .innerHTML =

    data
      .map(
        x => `

          <tr>

            <td>
              ${x.date}
            </td>

            <td>
              <b>
                ${x.subject}
              </b>
            </td>

            <td>
              ${x.time}
            </td>

            <td>
              ${statusHTML(
                x.status
              )}
            </td>

          </tr>

        `
      )
      .join("");

}


/* =====================================================
   DAFTAR SISWA
===================================================== */

function renderStudents() {

  const q =
    (
      $("studentSearch").value ||
      ""
    ).toLowerCase();


  const data =
    students.filter(
      s =>
        s.name
          .toLowerCase()
          .includes(q) ||
        s.nis.includes(q)
    );


  $("studentTable")
    .innerHTML =

    data
      .map(
        (s, i) => {

          const pct =
            Math.round(
              s.hadir /
              20 *
              100
            );


          return `

            <tr>

              <td>
                ${i + 1}
              </td>

              <td>
                ${s.nis}
              </td>

              <td>
                <b>
                  ${s.name}
                </b>
              </td>

              <td>
                ${s.hadir}
              </td>

              <td>
                ${s.izin}
              </td>

              <td>
                ${s.sakit}
              </td>

              <td>
                <b>
                  ${pct}%
                </b>
              </td>

            </tr>

          `;

        }
      )
      .join("");

}


/* =====================================================
   JADWAL
===================================================== */

function renderSchedule() {

  $("scheduleGrid")
    .innerHTML =

    schedule
      .map(
        d => `

          <div class="card day-card">

            <h3>
              ${d.day}
            </h3>

            ${d.lessons
              .map(
                lesson => `

                  <div class="lesson">

                    <b>
                      ${lesson[1]}
                    </b>

                    <span>
                      🕐 ${lesson[0]}
                      •
                      📍 ${lesson[2]}
                    </span>

                  </div>

                `
              )
              .join("")}

          </div>

        `
      )
      .join("");

}


/* =====================================================
   STATUS CLASS ABSENSI
===================================================== */

function getStatusClass(status) {

  switch (status) {

    case "Hadir":
      return "status-hadir";

    case "Izin":
      return "status-izin";

    case "Sakit":
      return "status-sakit";

    case "Alpha":
      return "status-alpha";

    case "Terlambat":
      return "status-terlambat";

    default:
      return "";

  }

}


/* =====================================================
   RENDER TABEL ABSENSI
===================================================== */

function renderAttendanceTable() {

  const tbody =
    $("attendanceTableBody");


  if (!tbody)
    return;


  tbody.innerHTML = "";


  students.forEach(
    (student, index) => {


      const data =
        attendanceData[
          student.nis
        ] || {

          status: "Alpha",

          jamMasuk: "",

          jamKeluar: "",

          keterangan: ""

        };


      const tr =
        document.createElement(
          "tr"
        );


      tr.innerHTML = `

        <td>
          ${index + 1}
        </td>


        <td>
          ${student.nis}
        </td>


        <td>
          ${student.name}
        </td>


        <td>

          <select

            class="status-select ${getStatusClass(
              data.status
            )}"

            data-nis="${student.nis}"

            onchange="
              changeAttendanceStatus(this)
            "

          >

            <option
              value="Hadir"
              ${
                data.status ===
                "Hadir"
                  ? "selected"
                  : ""
              }
            >
              Hadir
            </option>


            <option
              value="Izin"
              ${
                data.status ===
                "Izin"
                  ? "selected"
                  : ""
              }
            >
              Izin
            </option>


            <option
              value="Sakit"
              ${
                data.status ===
                "Sakit"
                  ? "selected"
                  : ""
              }
            >
              Sakit
            </option>


            <option
              value="Alpha"
              ${
                data.status ===
                "Alpha"
                  ? "selected"
                  : ""
              }
            >
              Alpha
            </option>


            <option
              value="Terlambat"
              ${
                data.status ===
                "Terlambat"
                  ? "selected"
                  : ""
              }
            >
              Terlambat
            </option>

          </select>

        </td>


        <td>

          <input

            type="time"

            value="${
              data.jamMasuk || ""
            }"

            data-field="jamMasuk"

            data-nis="${student.nis}"

            onchange="
              updateAttendanceField(this)
            "

          >

        </td>


        <td>

          <input

            type="time"

            value="${
              data.jamKeluar || ""
            }"

            data-field="jamKeluar"

            data-nis="${student.nis}"

            onchange="
              updateAttendanceField(this)
            "

          >

        </td>


        <td>

          <input

            type="text"

            value="${
              data.keterangan || ""
            }"

            placeholder="-"

            data-field="keterangan"

            data-nis="${student.nis}"

            onchange="
              updateAttendanceField(this)
            "

          >

        </td>

      `;


      tbody.appendChild(tr);

    }
  );


  updateAttendanceSummary();

}


/* =====================================================
   UBAH STATUS
===================================================== */

function changeAttendanceStatus(select) {

  const nis =
    select.dataset.nis;


  const status =
    select.value;


  if (!attendanceData[nis]) {

    attendanceData[nis] = {

      status: "Alpha",

      jamMasuk: "",

      jamKeluar: "",

      keterangan: ""

    };

  }


  attendanceData[nis].status =
    status;


  select.className =
    `status-select ${getStatusClass(
      status
    )}`;


  if (
    status === "Izin" ||
    status === "Sakit" ||
    status === "Alpha"
  ) {

    attendanceData[nis].jamMasuk =
      "";

    attendanceData[nis].jamKeluar =
      "";

  }


  saveAttendanceData();


  updateAttendanceSummary();

}


/* =====================================================
   UPDATE FIELD
===================================================== */

function updateAttendanceField(input) {

  const nis =
    input.dataset.nis;


  const field =
    input.dataset.field;


  if (!attendanceData[nis]) {

    attendanceData[nis] = {

      status: "Alpha",

      jamMasuk: "",

      jamKeluar: "",

      keterangan: ""

    };

  }


  attendanceData[nis][field] =
    input.value;


  saveAttendanceData();

}


/* =====================================================
   REKAP TABEL
===================================================== */

function updateAttendanceSummary() {

  let hadir = 0;

  let izin = 0;

  let sakit = 0;

  let alpha = 0;

  let terlambat = 0;


  students.forEach(
    student => {

      const data =
        attendanceData[
          student.nis
        ];


      if (!data)
        return;


      switch (data.status) {

        case "Hadir":
          hadir++;
          break;

        case "Izin":
          izin++;
          break;

        case "Sakit":
          sakit++;
          break;

        case "Alpha":
          alpha++;
          break;

        case "Terlambat":
          terlambat++;
          break;

      }

    }
  );


  $("summaryHadir")
    .textContent =
    hadir;


  $("summaryIzin")
    .textContent =
    izin;


  $("summarySakit")
    .textContent =
    sakit;


  $("summaryAlpha")
    .textContent =
    alpha;


  $("summaryTerlambat")
    .textContent =
    terlambat;

}


/* =====================================================
   SIMPAN SEMUA
===================================================== */

function saveAllAttendance() {

  saveAttendanceData();


  showToast(
    "Semua data absensi berhasil disimpan."
  );

}


/* =====================================================
   RESET
===================================================== */

function resetAttendance() {

  const yakin =
    confirm(
      "Yakin ingin mereset data absensi?"
    );


  if (!yakin)
    return;


  attendanceData =
    createDefaultAttendance();


  saveAttendanceData();


  renderAttendanceTable();


  showToast(
    "Data absensi berhasil direset."
  );

}


/* =====================================================
   DOM READY
===================================================== */

document.addEventListener(
  "DOMContentLoaded",
  () => {


    /* LOGIN */

    $("loginForm")
      .addEventListener(
        "submit",
        login
      );


    /* LOGOUT */

    $("logoutBtn")
      .addEventListener(
        "click",
        logout
      );


    /* MENU MOBILE */

    $("menuBtn")
      .addEventListener(
        "click",
        () =>
          $("sidebar")
            ?.classList
            .toggle("open")
      );


    /* NAVIGASI */

    document
      .querySelectorAll(
        ".nav-item"
      )
      .forEach(
        btn =>
          btn.addEventListener(
            "click",
            () =>
              navigate(
                btn.dataset.page
              )
          )
      );


    /* FILTER RIWAYAT */

    $("historyFilter")
      .addEventListener(
        "change",
        renderHistory
      );


    /* SEARCH SISWA */

    $("studentSearch")
      .addEventListener(
        "input",
        renderStudents
      );


    /* TANGGAL ABSENSI */

    $("attendanceDate")
      .value =
      getTodayDate();


    /* TOMBOL TAMPILKAN */

    $("showAttendanceBtn")
      .addEventListener(
        "click",
        () => {

          renderAttendanceTable();

          showToast(
            "Data absensi berhasil ditampilkan."
          );

        }
      );


    /* SIMPAN SEMUA */

    $("saveAllAttendanceBtn")
      .addEventListener(
        "click",
        saveAllAttendance
      );


    /* RESET */

    $("resetAttendanceBtn")
      .addEventListener(
        "click",
        resetAttendance
      );


    /* LOAD DATABASE LOCAL */

    loadAttendanceData();


    /* RENDER */

    renderAll();

  }
);