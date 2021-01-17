#include <iostream>
#include <random>

int main()
{
    using namespace std;
    default_random_engine e;
    auto wienerProcess = [e](float s) mutable -> float {
        normal_distribution<float> normal(0, 1);//均值0，方差1
        return normal(e) + s;
    };

    cout << "wiener process:" << endl;

    float tmp = 0;
    for (int i = 0; i < 100; i++) {
        cout << i << "," << tmp << endl;
        tmp = wienerProcess(tmp);
    }
    return 0;
}
