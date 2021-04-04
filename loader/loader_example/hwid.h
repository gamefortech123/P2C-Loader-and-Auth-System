#pragma once

#include <locale>
#include <sstream>
#include <shlobj.h>
#include <comdef.h>
#include <Wbemidl.h>
#include <cassert>
#include <atlstr.h>

namespace hwid {
	static DWORD GetPhysicalDriveSerialNumber(UINT drive_to_print, CString& strSerialNumber) {
		DWORD dwResult = NO_ERROR;
		strSerialNumber.Empty();

		CString strDrivePath;
		strDrivePath.Format(_T("\\\\.\\PhysicalDrive%u"), drive_to_print);

		HANDLE hDevice = CreateFile(strDrivePath, 0, FILE_SHARE_READ | FILE_SHARE_WRITE,
			NULL, OPEN_EXISTING, 0, NULL);

		if (INVALID_HANDLE_VALUE == hDevice)
			return GetLastError();

		STORAGE_PROPERTY_QUERY storagePropertyQuery;
		ZeroMemory(&storagePropertyQuery, sizeof(STORAGE_PROPERTY_QUERY));
		storagePropertyQuery.PropertyId = StorageDeviceProperty;
		storagePropertyQuery.QueryType = PropertyStandardQuery;

		STORAGE_DESCRIPTOR_HEADER storageDescriptorHeader = { 0 };
		DWORD dwBytesReturned = 0;
		if (!DeviceIoControl(hDevice, IOCTL_STORAGE_QUERY_PROPERTY, &storagePropertyQuery, sizeof(STORAGE_PROPERTY_QUERY), &storageDescriptorHeader, sizeof(STORAGE_DESCRIPTOR_HEADER), &dwBytesReturned, NULL)) {
			dwResult = GetLastError();
			CloseHandle(hDevice);
			return dwResult;
		}

		const DWORD dwOutBufferSize = storageDescriptorHeader.Size;
		BYTE* pOutBuffer = new BYTE[dwOutBufferSize];
		ZeroMemory(pOutBuffer, dwOutBufferSize);

		if (!DeviceIoControl(hDevice, IOCTL_STORAGE_QUERY_PROPERTY, &storagePropertyQuery, sizeof(STORAGE_PROPERTY_QUERY), pOutBuffer, dwOutBufferSize, &dwBytesReturned, NULL)) {
			dwResult = ::GetLastError();
			delete[]pOutBuffer;
			CloseHandle(hDevice);
			return dwResult;
		}

		STORAGE_DEVICE_DESCRIPTOR* pDeviceDescriptor = (STORAGE_DEVICE_DESCRIPTOR*)pOutBuffer;
		const DWORD dwSerialNumberOffset = pDeviceDescriptor->SerialNumberOffset;
		if (dwSerialNumberOffset != 0)
			strSerialNumber = CString(pOutBuffer + dwSerialNumberOffset);

		// perform cleanup and return
		delete[]pOutBuffer;
		CloseHandle(hDevice);
		return dwResult;
	}
}