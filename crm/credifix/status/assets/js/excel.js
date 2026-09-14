// assets/js/excel.js - Excel Export and Import Controller using SheetJS

const ExcelHandler = {
    // Export current clients to Excel file
    exportToExcel: function(clients) {
        if (!clients || clients.length === 0) {
            alert('No client records to export.');
            return;
        }

        const dataToExport = clients.map(client => ({
            'Client ID': 'CL-' + String(client.id).padStart(4, '0'),
            'Client Name': client.name || '',
            'Phone Number': client.phone || '',
            'Email': client.email || '',
            'Bank Name': client.bank_name || '',
            'Amount (₹)': client.amount ? Number(client.amount) : 0,
            'Current Status': client.status || '',
            'Additional Details': client.additional_details || '',
            'Created Date': client.created_at || '',
            'Last Updated': client.updated_at || ''
        }));

        const worksheet = XLSX.utils.json_to_sheet(dataToExport);
        
        // Auto-fit column widths
        const colWidths = [
            { wch: 12 }, // ID
            { wch: 22 }, // Name
            { wch: 16 }, // Phone
            { wch: 25 }, // Email
            { wch: 20 }, // Bank
            { wch: 14 }, // Amount
            { wch: 26 }, // Status
            { wch: 35 }, // Details
            { wch: 20 }, // Created
            { wch: 20 }  // Updated
        ];
        worksheet['!cols'] = colWidths;

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Clients Status Report');

        const fileName = `Status_Report_${new Date().toISOString().slice(0,10)}.xlsx`;
        XLSX.writeFile(workbook, fileName);
    },

    // Parse uploaded Excel file and return array of objects
    parseExcelFile: function(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheetName];
                    const json = XLSX.utils.sheet_to_json(worksheet, { defval: '' });
                    resolve(json);
                } catch (err) {
                    reject(err);
                }
            };
            reader.onerror = function(err) {
                reject(err);
            };
            reader.readAsArrayBuffer(file);
        });
    }
};
