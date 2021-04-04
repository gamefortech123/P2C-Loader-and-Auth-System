#include <windows.h>
#include <TlHelp32.h>
#include <thread>

#include "xor.h"
#include "needle.h"

bool Loader::process_running() {
	auto proc = this->get_pid(XorStr("WHAT YOU WANT TO INJECT TOO"));
	if (!proc) return 0;
	this->process_id = proc;
	return 1;
}

bool Loader::inject(uint8_t file[]) {
	auto handle = OpenProcess(PROCESS_ALL_ACCESS, FALSE, this->process_id);
	if (!handle || GetLastError() == ERROR_ACCESS_DENIED) {
		return false;
	}

	auto pIDH = (PIMAGE_DOS_HEADER)&file[0];
	auto pINH = (PIMAGE_NT_HEADERS64)((ULONGLONG)&file[0] + pIDH->e_lfanew);
	auto pISH = (PIMAGE_SECTION_HEADER)(pINH + 1);
	auto ManualInject = MANUAL_INJECT();

	if (pIDH->e_magic != IMAGE_DOS_SIGNATURE) return NULL;
	if (pINH->Signature != IMAGE_NT_SIGNATURE) return NULL;
	if (!(pINH->FileHeader.Characteristics & IMAGE_FILE_DLL)) return NULL;

	auto far_image = VirtualAllocEx(handle, NULL, pINH->OptionalHeader.SizeOfImage, MEM_COMMIT | MEM_RESERVE, PAGE_EXECUTE_READWRITE);
	if (!WriteProcessMemory(handle, far_image, &file[0], pINH->OptionalHeader.SizeOfHeaders, NULL)) return NULL;

	for (auto i = 0; i < pINH->FileHeader.NumberOfSections; i++) WriteProcessMemory(handle, (PVOID)((ULONGLONG)far_image + pISH[i].VirtualAddress), (PVOID)((ULONGLONG)&file[0] + pISH[i].PointerToRawData), pISH[i].SizeOfRawData, NULL);

	auto far_loader = VirtualAllocEx(handle, NULL, 4096, MEM_COMMIT | MEM_RESERVE, PAGE_EXECUTE_READWRITE);

	memset(&ManualInject, 0, sizeof(MANUAL_INJECT));
	ManualInject.ImageBase = far_image;
	ManualInject.NtHeaders = (PIMAGE_NT_HEADERS64)((ULONGLONG)far_image + pIDH->e_lfanew);
	ManualInject.BaseRelocation = (PIMAGE_BASE_RELOCATION)((ULONGLONG)far_image + pINH->OptionalHeader.DataDirectory[IMAGE_DIRECTORY_ENTRY_BASERELOC].VirtualAddress);
	ManualInject.ImportDirectory = (PIMAGE_IMPORT_DESCRIPTOR)((ULONGLONG)far_image + pINH->OptionalHeader.DataDirectory[IMAGE_DIRECTORY_ENTRY_IMPORT].VirtualAddress);
	ManualInject.fnLoadLibraryA = LoadLibraryA;
	ManualInject.fnGetProcAddress = GetProcAddress;

	WriteProcessMemory(handle, far_loader, &ManualInject, sizeof(MANUAL_INJECT), NULL);
	WriteProcessMemory(handle, (PVOID)((MANUAL_INJECT*)far_loader + 1), LibraryLoader, (ULONGLONG)stub - (ULONGLONG)LibraryLoader, NULL);
	auto hThread = CreateRemoteThread(handle, NULL, 0, (LPTHREAD_START_ROUTINE)((MANUAL_INJECT*)far_loader + 1), far_loader, 0, NULL);
	if (!hThread) {
		return false;
	}

	CloseHandle(handle);
	return true;
}
