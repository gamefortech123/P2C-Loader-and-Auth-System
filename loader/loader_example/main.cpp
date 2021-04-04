#include "auth.h"
#include "needle.h"
#include "public.h"
#include "private.h"
#include <iostream>
#include <thread>
#include "hwid.h"
#include <random>

const char* ws = " \t\n\r\f\v";
std::string& ltrim(std::string& s, const char* t = ws) {
	s.erase(0, s.find_first_not_of(t));
	return s;
}

static std::string random_string(int size) {
	std::string str(XorStr("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"));

	std::random_device rd;
	std::mt19937 generator(rd());

	std::shuffle(str.begin(), str.end(), generator);

	return str.substr(0, size);
}

bool file_exists(const std::string dir) {
	struct stat buffer;
	return (stat(dir.c_str(), &buffer) == 0);
}

bool update_client() {
	TCHAR current_directory[MAX_PATH];
	GetCurrentDirectory(MAX_PATH, current_directory);

	char buffer[MAX_PATH] = {};
	sprintf(buffer, XorStr("%s\\%s.exe"), current_directory, random_string(10).c_str());

	auto result = URLDownloadToFileA(NULL, XorStr("SERVER IP HERE/latest.bin"), buffer, NULL, NULL);
	if (result != S_OK) {
		return false;
	}

	TCHAR szModuleName[MAX_PATH];
	GetModuleFileName(NULL, szModuleName, MAX_PATH);
	char previous_location[MAX_PATH] = {};
	sprintf(previous_location, XorStr("%s\\old.exe"), current_directory);
	if (!std::rename(szModuleName, previous_location)) { // SUCCESS
		STARTUPINFO si;
		PROCESS_INFORMATION pi;
		ZeroMemory(&si, sizeof(si));
		si.cb = sizeof(si);
		ZeroMemory(&pi, sizeof(pi));

		CreateProcessA((LPCSTR)buffer, NULL, NULL, NULL, FALSE, CREATE_NEW_CONSOLE, NULL, NULL, &si, &pi);

		CloseHandle(pi.hProcess);
		CloseHandle(pi.hThread);
		return true;
	}
	
	return false;
}

int main() {
	TCHAR current_directory[MAX_PATH];
	GetCurrentDirectory(MAX_PATH, current_directory);

	char old_location[512] = {};
	sprintf(old_location, XorStr("%s\\old.exe"), current_directory);
	if (file_exists(old_location) && !std::remove(old_location)) {
		printf(XorStr("loader updated."));
		std::this_thread::sleep_for(std::chrono::milliseconds(1250));
	}

	auto auth = new Auth();

	if (auth->needs_update()) {
		MessageBoxA(NULL, XorStr("update avaliable"), XorStr(""), NULL);
		if (update_client()) {
			ExitProcess(0);
		} else {
			MessageBoxA(NULL, XorStr("failed to update"), XorStr(""), NULL);
		}
	}

	while (true) {
		system(XorStr("cls"));
		if (!auth->remember_credentials()) {
			printf(XorStr("key: "));
			std::cin >> auth->product_key;
		}
		SetConsoleTitle(_T("welcome"));
		std::cout << (XorStr("==============================================")) << std::endl;
		std::cout << (XorStr("                  Rust Script")) << std::endl;
		std::cout << (XorStr("==============================================")) << std::endl;
		std::cout << (XorStr(" ")) << std::endl;
		std::cout << (XorStr("Version:      ")) << XorStr(VERISON) << std::endl;
		std::cout << (XorStr("Last Update:  ")) << __DATE__ << " " << __TIME__ << std::endl;
		printf(XorStr("Product Key:  %s\n"), auth->product_key);
		std::cout << (XorStr(" ")) << std::endl;
		std::cout << (XorStr("ID   NAME")) << std::endl;
		std::cout << (XorStr("[1]  public dll")) << std::endl;
		std::cout << (XorStr("[2]  private dll")) << std::endl;
		std::cout << (XorStr(" ")) << std::endl;
		printf(XorStr("Select app to load (Type [-1] to cancel):"));

		int option = 0;
		std::cin >> option;

		if (option != 1 && option != 2) {
			printf(XorStr("invalid version\n"));
			std::this_thread::sleep_for(std::chrono::seconds(3));
			return -1;
		}

		auto is_private = option == 2;
		CString drive_serial;
		hwid::GetPhysicalDriveSerialNumber(0, drive_serial);
		std::string driver_string((LPCTSTR)drive_serial);
		if (auth->authenticate(ltrim(driver_string).c_str(), is_private)) {
			system("cls");
			SetConsoleTitle(_T("loading..."));
			SetConsoleTextAttribute(GetStdHandle(STD_OUTPUT_HANDLE), 11);
			std::cout << (XorStr("==============================================")) << std::endl;
			std::cout << (XorStr("                  Rust Script")) << std::endl;
			std::cout << (XorStr("==============================================")) << std::endl;
			std::cout << (XorStr(" ")) << std::endl;
			std::cout << (XorStr("Version:      ")) << XorStr(VERISON) << std::endl;
			std::cout << (XorStr("Last Update:  ")) << __DATE__ << " " <<  __TIME__ << std::endl;
			printf(XorStr("Product Key:  %s\n"), auth->product_key);
			printf(XorStr("Remaining Time: %s\n"), auth->time_left);
			Sleep(5000);
			printf(XorStr("injecting...\n"));
			std::this_thread::sleep_for(std::chrono::seconds(5));
			auth->save_credentials();
			break;
		} else {
			printf(XorStr("%s"), auth->status.c_str());
			std::this_thread::sleep_for(std::chrono::seconds(3));
		}
	}
	std::this_thread::sleep_for(std::chrono::seconds(3));
	if (auth->is_authenticated) {
		auto injector = new Loader();
		if (!injector->process_running() || !injector->inject(auth->private_access ? private_bytes : public_bytes)) {
			MessageBoxA(NULL, XorStr("failed to inject"), NULL, NULL);
		}
	}
}