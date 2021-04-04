#include <Windows.h>
#include <string>
#include <sstream>
#include <fstream>
#include <filesystem>
#include <shlobj.h>
#include "xor.h"
#include <vector>
#pragma comment(lib, "shell32.lib")
#pragma comment(lib, "wininet.lib")
#pragma comment(lib, "Urlmon.lib")
#pragma comment(lib, "ws2_32.lib")
#pragma comment(lib, "winhttp.lib")
#pragma comment(lib, "wsock32.lib")

#define IS_BANNED XorStr("4e9mMzxqfRA4")
#define SUB_EXPIRED XorStr("6n9prTpS538B")
#define INVALID_HWID XorStr("byYkP36DrwwJ")
#define INVALID_PERMS XorStr("QtVhnzj9qmJR")
#define VERISON "1.0" // this is the verison number, will have to change when you update the loader

class Auth {
public:
	char product_key[64] = {};
	char time_left[64] = {};

	bool private_access = false;
	bool is_authenticated = false;
	bool remember_pass = true;
	bool driver_loaded = false;
	std::string status = "";
	std::string loader_version = XorStr(VERISON);

	bool needs_update() {
		return get_link(XorStr("SERVER IP HERE"), XorStr("/version.php")) != loader_version;
	}

	std::string encrypt(std::string str) {
		for (int i = 0; (i < 100 && str[i] != '\0'); i++)
			str[i] = str[i] + 2;

		return str;
	}

	std::string decrypt(std::string str) {
		for (int i = 0; (i < 100 && str[i] != '\0'); i++)
			str[i] = str[i] - 2;

		return str;
	}

	bool authenticate(const char* hwid, bool is_private) {
		char id_char[128];
		sprintf(id_char, "%d", GetTickCount64());

		char web_path[256];
		sprintf(web_path, XorStr("/auth.php?product_key=%s&hwid=%s&id=%s&module=%s"), product_key, hwid, id_char, is_private ? XorStr("script_private") : XorStr("script_public"));

		auto response = get_link(XorStr("SERVER IP HERE"), web_path);
		if (strstr(response.c_str(), id_char)) {
			auto split_response = split(response, XorStr(":"));
			if (split_response.size() > 1) {
				if (split_response[0] == XorStr("LIFETIME")) {
					sprintf(time_left, XorStr("unlimited time"));
				}
				else {
					sprintf(time_left, XorStr("%s day(s) %s hour(s)"), split_response[1].c_str(), split_response[2].c_str());
				}
			}

			if (remember_pass) {
				save_credentials();
			}

			status = XorStr("login successful.");
			is_authenticated = true;
			if (is_private) {
				private_access = true;
			}
			return true;
		} else if (strstr(response.c_str(), IS_BANNED)) {
			status = XorStr("user is banned");
			return false;
		} else if (strstr(response.c_str(), SUB_EXPIRED)) {
			status = XorStr("sub expired please buy more time");
			return false;
		} else if (strstr(response.c_str(), INVALID_HWID)) {
			status = XorStr("wrong hwid please contact support");
			return false;
		} else {
			status = XorStr("invalid login");
			return false;
		}
		return false;
	}

	bool remember_credentials() {
		char my_documents[MAX_PATH];
		SHGetFolderPath(NULL, CSIDL_PERSONAL, NULL, SHGFP_TYPE_CURRENT, my_documents);

		strcat(my_documents, XorStr("\\script"));
		std::filesystem::create_directories(my_documents);

		char file_location[128];
		sprintf(file_location, "%s\\key.ini", my_documents);

		std::ifstream ifs(file_location);
		if (!ifs.is_open()) {
			return false;
		}

		std::string content((std::istreambuf_iterator<char>(ifs)), (std::istreambuf_iterator<char>()));
		sprintf(product_key, "%s", content.c_str());
		return true;
	}

	void save_credentials() {
		char my_documents[MAX_PATH];
		SHGetFolderPath(NULL, CSIDL_PERSONAL, NULL, SHGFP_TYPE_CURRENT, my_documents);
		strcat(my_documents, XorStr("\\script"));
		std::filesystem::create_directories(my_documents);

		char file_location[128];
		sprintf(file_location, "%s\\key.ini", my_documents);

		std::ofstream credentials(file_location);

		credentials << product_key;
		credentials.close();
	}

	std::string get_link(const char* host, const char* path) {
		char location[128];
		sprintf(location, "http://%s%s", host, path);

		IStream* stream;
		HRESULT result = URLOpenBlockingStream(0, (LPCSTR)location, &stream, 0, 0);

		if (result != 0) {
			return XorStr("Failed");
		}
		char buffer[100];
		unsigned long bytesRead;
		std::stringstream ss;
		stream->Read(buffer, 100, &bytesRead);
		while (bytesRead > 0U)
		{
			ss.write(buffer, (long long)bytesRead);
			stream->Read(buffer, 100, &bytesRead);
		}
		stream->Release();

		return ss.str();
	}

	std::vector<std::string> split(const std::string& str, const std::string& delim) {
		std::vector<std::string> tokens;

		if (str.size() <= 1 || str.size() > 128)
			return tokens;

		size_t prev = 0, pos = 0;
		do
		{
			pos = str.find(delim, prev);
			if (pos == std::string::npos) pos = str.length();
			std::string token = str.substr(prev, pos - prev);
			if (!token.empty()) tokens.push_back(token);
			prev = pos + delim.length();
		} while (pos < str.length() && prev < str.length());
		return tokens;
	}
};