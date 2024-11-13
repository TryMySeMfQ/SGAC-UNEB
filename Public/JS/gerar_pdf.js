document.addEventListener('DOMContentLoaded', () => {
    const { jsPDF } = window.jspdf;

    function gerarPDF() {
        const doc = new jsPDF();
        doc.setFontSize(16);
        doc.text('Relatório de Atividades', 105, 20, { align: 'center' });

        const atividades = atividadesValidas; // Use a variável do PHP no script JS
        const atividadesValidadas = atividades.filter(atividade => atividade.validado);

        if (atividadesValidadas.length > 0) {
            let startY = 30;

            doc.setFontSize(12);
            doc.text('Descrição', 20, startY);
            doc.text('Horas', 120, startY);
            doc.text('Certificado', 160, startY);
            startY += 10;

            atividadesValidadas.forEach(atividade => {
                const descricao = atividade.descricao ? atividade.descricao : 'Descrição não disponível';
                const horas = atividade.horas ? atividade.horas.toString() : 'Horas não disponível';
                const certificado = atividade.certificado ? 'Sim' : 'Não';

                doc.text(descricao, 20, startY);
                doc.text(horas, 120, startY);
                doc.text(certificado, 160, startY);
                startY += 10;
            });

            const totalHoras = atividadesValidadas.reduce((sum, atividade) => sum + (atividade.horas || 0), 0);
            doc.text(`Total de Horas: ${totalHoras}`, 20, startY + 10);

            doc.save('relatorio_atividades.pdf');
        }
    }

    document.getElementById('gerarPDF').addEventListener('click', gerarPDF);
});
