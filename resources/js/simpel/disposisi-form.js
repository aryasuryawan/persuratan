document.addEventListener('DOMContentLoaded', () => {
    const jenisPelaksana = document.getElementById('jenis-pelaksana');
    const jenisLangsung = document.getElementById('jenis-langsung');
    const sectionPelaksana = document.getElementById('section-pelaksana');
    const sectionLangsung = document.getElementById('section-langsung');

    if (!jenisPelaksana || !jenisLangsung) return;
    if (!sectionPelaksana || !sectionLangsung) return;

    function toggleSections() {
        const pelaksanaChecked = document.querySelector('input[name="jenis"]:checked')?.value;
        if (pelaksanaChecked === 'ke_pelaksana') {
            sectionPelaksana.classList.remove('d-none');
            sectionLangsung.classList.add('d-none');
        } else if (pelaksanaChecked === 'langsung_pimpinan') {
            sectionPelaksana.classList.add('d-none');
            sectionLangsung.classList.remove('d-none');
        }
    }

    jenisPelaksana.addEventListener('change', toggleSections);
    jenisLangsung.addEventListener('change', toggleSections);
    toggleSections();
});
